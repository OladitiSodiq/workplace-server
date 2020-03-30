<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    Workplace_Authenticate
 * @copyright  Copyright (c) 2020 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Authenticate.php Monday 23rd of March 2020 09:36AM ayoola@ayoo.la $
 */

/**
 * @see PageCarton_Widget
 */

class Workplace_Authenticate extends Workplace
{
	
    /**
     * Access level for player. Defaults to everyone
     *
     * @var boolean
     */
	protected static $_accessLevel = array( 0 );
	
    /**
     * 
     * 
     * @var string 
     */
	protected static $_objectTitle = 'Authenticate Client'; 

    /**
     * Returns user info from auth token
     * 
     * @param array Auth Info
     * @return mixed
     * 
     */
	public static function getAuthUserInfo( array $authInfo )
    {
        $table = Workplace_Authenticate_Table::getInstance();

        $userIdentifier = array( 
            'user_id' => $authInfo['user_id'],
            'auth_token' => $authInfo['auth_token'],
        );
    //    var_export( $userIdentifier  );
    //    var_export( $table->select()  );
        if( ! $auth = $table->selectOne( null, $userIdentifier ) )
        {
            return false;
        }
    //    var_export( Application_User_Abstract::getUserInfo( array( 'user_id' => $authInfo['user_id'] ) ) );
        $userInfo = self::getUserInfo( array( 'email' => $auth['email'] ) );
        return $userInfo;
    }

    /**
     * Performs the whole widget running process
     * 
     */
	public function init()
    {    
		try
		{ 
            //  Code that runs the widget goes here...
        //    var_export( $_POST );
        //    $_POST['email'] = 'zzzzzzxxz@xx.com';
        //    $_POST['password'] = 'zzzzzzxxz@xx.com';
            if( empty( $_POST['email'] ) || empty( $_POST['password'] ) )
            {
                //  error
                $errorInfo = array(
                    'badnews' => 'email & password cannot be empty'
                );
                $this->_objectData = $errorInfo;
                return false;
            }
            $authInfo = array( 
                'email' => $_POST['email'],
                'password' => $_POST['password'],
            );

        //    var_export( $authInfo );
           
            if( $userInfo = Ayoola_Access_Login::localLogin( $authInfo ) )
            {
            //    var_export( $userInfo );
                $authToken = md5( uniqid( json_encode( $authInfo ), true ) );

                //  save auth info in data
                $table = Workplace_Authenticate_Table::getInstance();

                $authInfoToSave = array( 
                    'user_id' => strval( $userInfo['user_id'] ),
                    'email' => strtolower( $userInfo['email'] ),
                    'auth_token' => $authToken,
                    'device_info' => $_POST['device_info'],
                );
            //    var_export( $authInfoToSave );

                $table->insert( $authInfoToSave );
                $myWorkspaces = self::getAuthWorkspaces( $email );

                if( $myWorkspacesId = array_column( $myWorkspaces, 'workspace_id' ) )
                {
                    $myWorkspaces= array( 'workspace_id' => $myWorkspacesId );
                }

                $settings = Workplace_Settings::retrieve() ? : array();
                $this->_objectData += $authInfoToSave;
                $this->_objectData += $userInfo;
                $this->_objectData += $settings;
                $this->_objectData += $myWorkspaces;
            //    var_export( $this->_objectData  );

            }
            else
            {
                //  error
                $errorInfo = array(
                    'badnews' => 'Invalid email or password'
                );
                $this->_objectData = $errorInfo;
            }

             // end of widget process
          
		}  
		catch( Exception $e )
        { 
            //  Alert! Clear the all other content and display whats below.
        //    $this->setViewContent( self::__( '<p class="badnews">' . $e->getMessage() . '</p>' ) ); 
            $this->setViewContent( self::__( '<p class="badnews">Theres an error in the code</p>' ) ); 
            return false; 
        }
	}
	// END OF CLASS
}
