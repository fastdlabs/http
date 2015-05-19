<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/5/19
 * Time: 下午12:02
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */
$composer = include __DIR__ . '/../vendor/autoload.php';

$request = \Dobee\Http\Request::createGlobalRequest();

class MysqlHandler implements Dobee\Http\Session\SessionHandler
{
    /**
     * @var PDO
     */
    protected $pdo;

    //
    public function __construct()
    {

    }

    /**
     * PHP >= 5.4.0<br/>
     * Close the session
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     *       The return value (usually TRUE on success, FALSE on failure).
     *       Note this value is returned internally to PHP for processing.
     *       </p>
     */
    public function close()
    {
        $this->pdo = null;
        unset($this->pdo);
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Destroy a session
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param int $session_id The session ID being destroyed.
     * @return bool <p>
     *                        The return value (usually TRUE on success, FALSE on failure).
     *                        Note this value is returned internally to PHP for processing.
     *                        </p>
     */
    public function destroy($session_id)
    {
        $affected = $this->pdo->exec('delete from test_session where title = "' . $session_id . '"');

        return $affected ? $affected : false;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Cleanup old sessions
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     *                         Sessions that have not updated for
     *                         the last maxlifetime seconds will be removed.
     *                         </p>
     * @return bool <p>
     *                         The return value (usually TRUE on success, FALSE on failure).
     *                         Note this value is returned internally to PHP for processing.
     *                         </p>
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Initialize session
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path  The path where to store/retrieve the session.
     * @param string $session_id The session id.
     * @return bool <p>
     *                           The return value (usually TRUE on success, FALSE on failure).
     *                           Note this value is returned internally to PHP for processing.
     *                           </p>
     */
    public function open($save_path, $session_id)
    {
        $this->pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=test', 'root', '123456', [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
        ]);
    }

    /**
     * PHP >= 5.4.0<br/>
     * Read session data
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     *                           Returns an encoded string of the read data.
     *                           If nothing was read, it must return an empty string.
     *                           Note this value is returned internally to PHP for processing.
     *                           </p>
     */
    public function read($session_id)
    {
        return 'janhuang';
//        return $this->pdo->query('select * from test_session where title = "' .  $session_id . '"')->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * PHP >= 5.4.0<br/>
     * Write session data
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id   The session id.
     * @param string $session_data <p>
     *                             The encoded session data. This data is the
     *                             result of the PHP internally encoding
     *                             the $_SESSION superglobal to a serialized
     *                             string and passing it as this parameter.
     *                             Please note sessions use an alternative serialization method.
     *                             </p>
     * @return bool <p>
     *                             The return value (usually TRUE on success, FALSE on failure).
     *                             Note this value is returned internally to PHP for processing.
     *                             </p>
     */
    public function write($session_id, $session_data)
    {
//        list($title, $data) = explode('|', $session_data);
//        $row = $this->pdo->query('select * from test_session where session_id = \'' . $session_id . '\'')->fetch(PDO::FETCH_ASSOC);
//        if (!$row) {
//            var_dump($this->pdo->exec('replace insert into test_session (session_id, title, `value`) VALUES (\'' . $session_id . '\', "' . $title . '", \'' . $data. '\')'));
//        }
    }
}

/**
 * 因为session较为特殊，所以这里需要用`getSession`方法获取
 */
$session = $request->getSession(new MysqlHandler());
echo '<pre>';
print_r($session);
//$session->setSession('name', 'janhuang');

//echo $session->getSession('name');



