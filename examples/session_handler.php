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

class MysqlHandler extends \Dobee\Http\Session\SessionHandlerAbstract
{
    /**
     * @var PDO
     */
    protected $pdo;

    //
    public function __construct()
    {

    }

    public function close()
    {
        $this->pdo = null;
        unset($this->pdo);
        return true;
    }

    /**
     * 删除session
     */
    public function destroy($session_id)
    {
        $affected = $this->pdo->exec('delete from test_session where title = "' . $session_id . '"');

        return $affected ? $affected : false;
    }

    /**
     * 回收过时session
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    public function open($save_path, $session_id)
    {
        try {
            $this->pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=test', 'root', '123456', [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
            ]);
        } catch (PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * fill $_SESSION variable
     */
    public function read($session_id)
    {
        $result = $this->pdo->query('select * from test_session')->fetchAll(PDO::FETCH_ASSOC);
        if (!$result) {
            return '';
        }
        $sessions = $session_id;
        foreach ($result as $row) {
            $sessions .= $row['value'];
        }
        return $sessions;
    }

    public function write($session_id, $session_data)
    {
        if (empty($session_data)) {
            return false;
        }

        $sql = sprintf("REPLACE INTO test_session (`session_id`, `value`, `create_at`) VALUES ('%s', '%s', '%s')",
            $session_id,
            $session_data,
            time());
        return 1 == $this->pdo->exec($sql) ? true : false;
    }
}

/**
 * 因为session较为特殊，所以这里需要用`getSession`方法获取
 */
$session = $request->getSession();
echo '<pre>';
print_r($session);
//$session->setSession('name', 'janhuang');
//$session->setSession('age', '22');

//echo $session->getSession('name');



