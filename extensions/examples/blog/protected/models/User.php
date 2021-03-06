<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * User file
 * 
 * @package 	blog
 * @subpackage 	
 * 
 * @author 		Web Master <webmaster@example.com>
 * @version 	SVN: $Id: User.php 348 2009-12-24 08:46:38Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */
class User extends BaseModel
{
	//********************************************************************************
	//* Code Information
	//********************************************************************************
	
	/**
	* This model was generated from database component 'db'
	*
	* The followings are the available columns in table 'user_t':
	*
	* @var integer $id
	* @var string $user_name_text
	* @var string $password_text
	* @var string $email_addr_text
	* @var string $profile_text
	* @var string $create_date
	* @var string $lmod_date
	*/
	 
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Returns the static model of the specified AR class.
	* @return CActiveRecord the static model class
	*/
	public static function model( $sClassName = __CLASS__ )
	{
		return parent::model( $sClassName );
	}
	
	/**
	* @return string the associated database table name
	*/
	public function tableName()
	{
		return self::getTablePrefix() . 'user_t';
	}

	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		return array(
			array( 'user_name_text', 'length', 'max' => 30 ),
			array( 'password_text', 'length', 'max' => 30 ),
			array( 'email_addr_text', 'length', 'max' => 255 ),
			array( 'user_name_text, password_text, email_addr_text', 'required' ),
		);
	}

	/**
	* @return array relational rules.
	*/
	public function relations()
	{
		return array(
			'posts' => array( self::HAS_MANY, 'Post', 'author_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'user_name_text' => 'User Name Text',
			'password_text' => 'Password Text',
			'email_addr_text' => 'Email Addr Text',
			'profile_text' => 'Profile Text',
			'create_date' => 'Create Date',
			'lmod_date' => 'Lmod Date',
		);
	}

	/**
	 * @return array customized tooltips (attribute=>tip)
	 */
	public function attributeTooltips()
	{
		return array(
			'id' => 'Id',
			'user_name_text' => 'User Name Text',
			'password_text' => 'Password Text',
			'email_addr_text' => 'Email Addr Text',
			'profile_text' => 'Profile Text',
			'create_date' => 'Create Date',
			'lmod_date' => 'Lmod Date',
		);
	}
}
