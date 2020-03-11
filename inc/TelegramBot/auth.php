<?PHP

/**
 * Class Authentication
 *
 * Use default kOOL database connection and perform a user check on ko_leute
 */

class Authentication {

    public function __construct() {
    }

    /**
     * Check the $usertoken in db and return userid
	 *
     * @param String $usertoken to compare in db
     * @return Integer|Boolean $Userid if authentication is ok, otherwise FALSE
     */
    public function doAuthentication($usertoken) {
        if(strlen($usertoken) != 6) return FALSE;

        $where = "WHERE SUBSTRING(MD5(CONCAT(`id`, '". KOOL_ENCRYPTION_KEY ."')),11,6) = '".$usertoken."'";
        $data = db_select_data("ko_leute", $where,"id","","LIMIT 1",TRUE);

        if ($data['id'] != NULL) {
            return $data['id'];
        }
        return FALSE;
    }
}