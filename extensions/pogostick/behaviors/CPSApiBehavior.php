<?php
/*
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSApiBehavior provides a behavior to classes for making API calls
 *
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSApiBehavior.php 376 2010-03-22 21:34:12Z jerryablan@gmail.com $
 * @since 		v1.0.5
 *
 * @filesource
 */
class CPSApiBehavior extends CPSComponentBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* 'GET' Http method
	*/
	const HTTP_GET = 'GET';

	/**
	* 'PUT' Http method
	*/
	const HTTP_POST = 'POST';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	* Initialize
	*/
	public function preinit()
	{
		//	Call daddy...
		parent::preinit();

		//	Add ours...
		$this->addOptions( self::getBaseOptions() );

		//	Attach our events...
		$this->attachEventHandler( 'onBeforeApiCall', array( $this, 'beforeApiCall' ) );
		$this->attachEventHandler( 'onAfterApiCall', array( $this, 'afterApiCall' ) );
		$this->attachEventHandler( 'onRequestComplete', array( $this, 'requestComplete' ) );
	}

	/**
	* Allows for single behaviors
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	API options
				'altApiKey' => 'string:',
				'appendFormat' => 'boolean:false',
				'apiBaseUrl' => 'string:',
				'apiKey' => 'string:',
				'apiQueryName' => 'string:',
				'apiToUse' => 'string:',
				'apiSubUrls' => 'array:array()',
				'httpMethod' => 'string:' . self::HTTP_GET,
				'returnFormat' => 'string:',
				'requestData' => 'array:array()',
				'requestMap' => 'array:array()',
				'requireApiQueryName' => 'boolean:false',
				'testApiKey' => 'string:',
				'testAltApiKey' => 'string:',
				'userAgent' => 'string:Pogostick Yii Extensions; (+http://www.pogostick.com/yii)',
				'lastErrorMessage' => 'string:',
				'lastErrorMessageExtra' => 'string:',
				'lastErrorCode' => 'string:',
			)
		);
	}

	/**
	 * Make an HTTP request
	 *
	 * @param string $sUrl The URL to call
	 * @param string $sQueryString The query string to attach
	 * @param string $sMethod The HTTP method to use. Can be 'GET' or 'SET'
	 * @param integer $iTimeOut The number of seconds to wait for a response. Defaults to 60 seconds
	 * @param array $arHeaders Headers to add to the request
	 * @param function|array $oHeaderCallback The callback function to call after the header has been read. Accepts function reference or array( object, method )
	 * @param function|array $oReadCallback The callback function to call after the body has been read. Accepts function reference or array( object, method )
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	public function makeHttpRequest( $sUrl, $sQueryString = null, $sMethod = 'GET', $sUserAgent = null, $iTimeOut = 60, $arHeaders = null, $oHeaderCallback = null, $oReaderCallback = null )
	{
		//	Our user-agent string
		$_sAgent = ( null != $sUserAgent ) ? $sUserAgent : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)';

		//	Our return results
		$_sResult = null;

		// Use cURL
		if ( function_exists( 'curl_init' ) )
		{
			$_oCurl = curl_init();

			curl_setopt( $_oCurl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $_oCurl, CURLOPT_FAILONERROR, true );
			curl_setopt( $_oCurl, CURLOPT_USERAGENT, $_sAgent );
			curl_setopt( $_oCurl, CURLOPT_TIMEOUT, 60 );
			curl_setopt( $_oCurl, CURLOPT_VERBOSE, true );
			curl_setopt( $_oCurl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $_oCurl, CURLOPT_URL, $sUrl . ( 'GET' == $sMethod  ? ( ! empty( $sQueryString ) ? '?' . $sQueryString : '' ) : '' ) );

			if ( null != $arHeaders )
				curl_setopt( $_oCurl, CURLOPT_HTTPHEADER, $arHeaders );

			if ( null != $oHeaderCallback )
				cur_setopt( $_oCurl, CURLOPT_HEADERFUNCTION, $oHeaderCallback );

			if ( null != $oHeaderCallback )
				cur_setopt( $_oCurl, CURLOPT_READFUNCTION, $oReadCallback );

			//	If this is a post, we have to put the post data in another field...
			if ( 'POST' == $sMethod )
			{
				curl_setopt( $_oCurl, CURLOPT_URL, $sUrl );
				curl_setopt( $_oCurl, CURLOPT_POST, true );
				curl_setopt( $_oCurl, CURLOPT_POSTFIELDS, $sQueryString );
			}

			$_sResult = curl_exec( $_oCurl );
			$_e = curl_errno( $_oCurl );
			$_em = curl_error( $_oCurl );
			curl_close( $_oCurl );
		}
		else
			throw new CException( '"libcurl" is required to use this functionality. Please reconfigure your php.ini to include "libcurl".' );

		return( $_sResult );
	}

	/**
	* Adds to the requestMap array
	*
	* @param string $sLabel The "friendly" name for consumers
	* @param string $sParamName The name of the API variable, if null, $sLabel
	* @param bool $bRequired
	* @param array $arOptions
	* @param string $sApiName
	* @param string $sSubApiName
	* @return bool True if operation succeeded
	* @see makeMapItem
	* @see makeMapArray
	*/
	public function addRequestMapping( $sLabel, $sParamName = null, $bRequired = false, array $arOptions = null, $sApiName = null, $sSubApiName = null )
	{
		//	Save for next call
		static $_sLastApiName;
		static $_sLastAction;

		//	Set up statics so next call can omit those parameters.
		if ( null !== $sApiName && $sApiName != $_sLastApiName )
			$_sLastApiName = $sApiName;

		//	Make sure sub API name is set...
		if ( null === $_sLastAction && null == $sSubApiName )
			$sSubApiName = '/';

		if (  null !== $sSubApiName && $sSubApiName != $_sLastAction )
			$_sLastAction = $sSubApiName;

		//	Build the options
		$_arTemp = array( 'name' => ( null !== $sParamName ) ? $sParamName : $sLabel, 'required' => $bRequired );

		//	Add on any supplied options
		if ( null != $arOptions )
			$_arTemp = array_merge( $_arTemp, $arOptions );

		//	Add the mapping...
		if ( null == $_sLastApiName && null == $_sLastAction )
			return false;

		//	Add mapping...
		if ( ! $_arMap = $this->getValue( 'requestMap' ) )
			$_arMap = array();

		$_arMap[$_sLastApiName][$_sLastAction][$sLabel] = $_arTemp;
		$this->setValue( 'requestMap', $_arMap );

		return true;
	}

	/**
	* Makes the actual HTTP request based on settings
	*
	* @param string $sSubType
	* @param array $arRequestData
	* @return string
	*/
	protected function makeRequest( $sSubType = '/', $arRequestData = null, $sMethod = 'GET' )
	{
		//	Make sure apiQueryName is set...
		if ( $this->requireApiQueryName && $this->isEmpty( $this->apiQueryName ) )
		{
			throw new CException(
				Yii::t(
					__CLASS__,
					'Required option "apiQueryName" is not set.'
				)
			);
		}

		//	Default...
		$_arRequestData = $this->requestData;

		//	Check data...
		if ( null != $arRequestData && is_array( $arRequestData ) ) $_arRequestData = array_merge( $_arRequestData, $arRequestData );

		//	Check subtype...
		if ( ! empty( $sSubType ) && is_array( $this->requestMap[ $this->apiToUse ] ) )
		{
			if ( ! array_key_exists( $sSubType, $this->requestMap[ $this->apiToUse ] ) )
			{
				throw new CException(
					Yii::t(
						__CLASS__,
						'Invalid API SubType specified for "{apiToUse}". Valid subtypes are "{subTypes}"',
						array(
							'{apiToUse}' => $this->apiToUse,
							'{subTypes}' => implode( ', ', array_keys( $this->requestMap[ $this->apiToUse ] ) )
						)
					)
				);
			}
		}

		//	First build the url...
		$_sUrl = $this->apiBaseUrl .
			( substr( $this->apiBaseUrl, strlen( $this->apiBaseUrl ) - 1, 1 ) != '/' ? '/' : '' ) .
			( ( isset( $this->apiSubUrls[ $this->apiToUse ] ) && '/' != $this->apiSubUrls[ $this->apiToUse ] ) ? $this->apiSubUrls[ $this->apiToUse ] : '' );

		//	Add the API key...
		if ( $this->requireApiQueryName ) $_sQuery = $this->apiQueryName . '=' . $this->apiKey;

		//	Add the request data to the Url...
		if ( is_array( $this->requestMap ) && ! empty( $sSubType ) && isset( $this->requestMap[ $this->apiToUse ] ) )
		{
			$_arRequestMap = $this->requestMap[ $this->apiToUse ][ $sSubType ];
			$_arDone = array();

			//	Add any extra requestData parameters unchecked to the query string...
			foreach ( $_arRequestData as $_sKey => $_sValue )
			{
				if ( ! array_key_exists( $_sKey, $_arRequestMap ) )
				{
					$_sQuery .= '&' . $_sKey . '=' . urlencode( $_sValue );
					unset( $_arRequestData[ $_sKey ] );
				}
			}

			//	Now build the url...
			foreach ( $_arRequestMap as $_sKey => $_arInfo )
			{
				//	Tag as done...
				$_arDone[] = $_sKey;

				//	Is there a default value?
				if ( isset( $_arInfo[ 'default' ] ) && ! isset( $_arRequestData[ $_sKey ] ) ) $_arRequestData[ $_sKey ] = $_arInfo[ 'default' ];

				if ( isset( $_arInfo[ 'required' ] ) && $_arInfo[ 'required' ] && ! array_key_exists( $_sKey, $_arRequestData ) )
				{
					throw new CException(
						Yii::t(
							__CLASS__,
							'Required parameter {param} was not included in requestData',
							array(
								'{param}' => $_sKey,
							)
						)
					);
				}

				//	Add to query string
				if ( isset( $_arRequestData[ $_sKey ] ) ) $_sQuery .= '&' . $_arInfo[ 'name' ] . '=' . urlencode( $_arRequestData[ $_sKey ] );
			}
		}
		//	Handle non-requestMap call...
		else if ( is_array( $_arRequestData ) )
		{
			foreach ( $_arRequestData as $_sKey => $_oValue )
			{
				if ( isset( $_arRequestData[ $_sKey ] ) ) $_sQuery .= '&' . $_sKey . '=' . urlencode( $_oValue );
			}
		}

		CPSLog::trace( __METHOD__, 'Calling onBeforeApiCall' );

		//	Handle events...
		$_oEvent = new CPSApiEvent( $_sUrl, $_sQuery, null, $this );
		$this->onBeforeApiCall( $_oEvent );

		CPSLog::trace( __METHOD__, 'Making request: ' . $_sQuery );

		//	Ok, we've build our request, now let's get the results...
		$_sResults = CPSHelperBase::makeHttpRequest( $_sUrl, $_sQuery, $sMethod, $this->userAgent );

		CPSLog::trace( __METHOD__, 'Call complete: ' . var_export( $_sResults, true ) );

		//	Handle events...
		$_oEvent->urlResults = $_sResults;
		$this->onAfterApiCall( $_oEvent );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->returnFormat )
		{
			case 'xml':
				$_sResults = CPSTransform::arrayToXml( json_decode( $_sResults, true ), 'Results' );
				break;

			case 'array':
				$_sResults = json_decode( $_sResults, true );
				break;

			default:	//	Naked
				break;
		}

		//	Raise our completion event...
		$_oEvent->setUrlResults( $_sResults );
		$this->onRequestComplete( $_oEvent );

		//	Return results...
		return $_sResults;
	}

	//********************************************************************************
	//* Events and Handlers
	//********************************************************************************

	/**
	* Raises the onBeforeApiCall event
	*
	* @param CPSApiEvent $event
	*/
	public function onBeforeApiCall( CPSApiEvent $event )
	{
		$this->raiseEvent( 'onBeforeApiCall', $event);
	}

	public function beforeApiCall( CPSApiEvent $event )
	{
		return true;
	}

	/**
	* Raises the onAfterApiCall event. $event contains "raw" return data
	*
	* @param CPSApiEvent $event
	*/
	public function onAfterApiCall( CPSApiEvent $event )
	{
		$this->raiseEvent( 'onAfterApiCall', $event );
	}

	public function afterApiCall( CPSApiEvent $event )
	{
		return true;
	}

	/**
	* Raises the onRequestComplete event. $event contains "processed" return data (if applicable)
	*
	* @param CPSApiEvent $event
	*/
	public function onRequestComplete( CPSApiEvent $event )
	{
		$this->raiseEvent( 'onRequestComplete', $event );
	}

	public function requestComplete( CPSApiEvent $event )
	{
		return true;
	}

}