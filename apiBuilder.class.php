<?php
/**
 * Create restful api with ApiBuilder.
 * @author: DaoYoung
 * @date: 2017/5/25
 */
define('APP_PATH', __DIR__ . '/');
define('LIB_PATH', APP_PATH . 'Lib/');
define('TEST_PATH', APP_PATH . 'Test/');
$superAction = ['Home' => 'APICommonAction', 'Admin' => 'AdminCommonAction'];
$service = new ApiBuilder($superAction);
$service->run();

class ApiBuilder {

    private $baseActionArr;

    public function __construct($superAction)
    {
        $this->baseActionArr = $superAction;
    }

    public function run()
    {
        echo "\nApiBuilder 1.0, create api with ThinkPHP." . "\n";
        if ($this->getNick() == '') {
            echo 'Input your nick name:' . "\n";
            $this->setNick(trim(fgets(STDIN)));
        }
        echo "\n--------------- Now, we begin to create Action|Model|UnitTest! ---------------\n\n";
        echo 'Input table name:' . "\n";
        $table = $tableOriginal = trim(fgets(STDIN));
        if (substr($tableOriginal, -1) == 's') {
            $table = substr($tableOriginal, 0, -1);
        }
        $model = implode("", array_map("ucfirst", explode('_', $table)));
        $dir = "";
        while ($dir == "" || !array_key_exists($dir, $this->baseActionArr)) {
            echo 'Select action in list: [' . implode(',', array_keys($this->baseActionArr)) . ']:' . "\n";
            $dir = ucfirst(trim(fgets(STDIN)));
        }
        $this->init($dir, $model, $tableOriginal);
    }

    private function setNick($nick)
    {
        $file = APP_PATH . 'Runtime/Cache/ApiBuilderLocalNick';
        $myfile = fopen($file, "w+");
        fwrite($myfile, $nick);
        fclose($myfile);
        echo($nick);
        exit;
    }

    private function getNick()
    {
        $file = APP_PATH . 'Runtime/Cache/ApiBuilderLocalNick';
        if (!file_exists($file)) {
            return '';
        }
        $myfile = fopen($file, "r");
        $nick = fgets($myfile);
        fclose($myfile);

        return $nick;
    }

    function init($dir, $model, $table = '')
    {
        echo "\n\nApiBuilder RESULTS:\n\n";
        $this->initCreate($dir, $model, 'action');
        $this->initCreate($dir, $model, 'model', $table);
        $this->initCreate($dir, $model, 'actionTest');
        $this->initCreate($dir, $model, 'modelTest');
    }

    private function initCreate($dir, $model, $type, $table = '')
    {
        /**
         * @see ApiBuilder::initAction
         * @see ApiBuilder::initModel
         * @see ApiBuilder::initActionTest
         * @see ApiBuilder::initModelTest
         */
        $func = "init" . ucfirst($type);
        switch ($type) {
            case 'action':
                $className = 'API' . $model . 'Action';
                $file = LIB_PATH . 'Action/' . $dir . '/' . $className . '.class.php';
                $extendClass = $this->baseActionArr[$dir];
                break;
            case 'model':
                $extendClass = 'BaseModel';
                $className = $model . 'Model';
                $file = LIB_PATH . 'Model/' . $className . '.class.php';
                break;
            case 'actionTest':
                echo 'Do you want test API, ' . "Y/N ?\n";
                $answer = strtolower(trim(fgets(STDIN)));
                if ($answer == 'y') {
                    $extendClass = 'TestCase#' . $dir;
                    $className = 'API' . $model . 'ActionTest';
                    $file = TEST_PATH . 'ApiTest/' . $className . '.class.php';
                } else {
                    return;
                }
                break;
            case 'modelTest':
                echo 'Do you want test Model, ' . "Y/N ?\n";
                $answer = strtolower(trim(fgets(STDIN)));
                if ($answer == 'y') {
                    $extendClass = 'TestCase';
                    $className = $model . 'ModelTest';
                    $file = TEST_PATH . 'ModelTest/' . $className . '.class.php';
                } else {
                    return;
                }
                break;
        }
        if (file_exists($file)) {
            echo "\n" . $file . ' is exist!' . "\n\n" . "Cover it, Y/N ?\n";
            $answer = strtolower(trim(fgets(STDIN)));
            if ($answer == 'y') {
                $this->{$func}($file, $className, $extendClass, $table);
            } else {
                return;
            }
        } else {
            $this->{$func}($file, $className, $extendClass, $table);
        }
    }

    private function initAction($file, $actionName, $extendClass)
    {
        $date = date('Y/m/d');
        $nick = $this->getNick();
        $myfile = fopen($file, "w");
        $code = <<<EOT
<?php
/**
 * @author: $nick
 * @date: $date
 */
class $actionName extends $extendClass
{
    public function get_index()
    {
        return parent::_index();
    }

    public function post_add()
    {
        return parent::_insert();
    }

    public function post_edit()
    {
        return parent::_update();
    }

    public function delete_index()
    {
        return parent::_delete();
    }
}
EOT;
        fwrite($myfile, $code);
        fclose($myfile);
        $doc = array_flip($this->baseActionArr);
        echo "............ " . $doc[$extendClass] . '/' . $actionName . " was created!!!\n";
    }

    private function initModel($file, $modelName, $extendClass, $table)
    {
        $date = date('Y/m/d');
        $nick = $this->getNick();
        $myfile = fopen($file, "w");
        $code = <<<EOT
<?php
/**
 * @author: $nick
 * @date: $date
 */
class $modelName extends $extendClass
{
    protected \$tableName = "$table";
}
EOT;
        fwrite($myfile, $code);
        fclose($myfile);
        echo "............ " . $modelName . " was created!!!\n";
    }

    private function initActionTest($file, $actionName, $extendClass)
    {
        $date = date('Y/m/d');
        $nick = $this->getNick();
        $temp = explode('#', $extendClass);
        $extendClass = $temp[0];
        $group = $temp[1];
        $apiAction = substr($actionName, 0, -4);
        $myfile = fopen($file, "w");
        $code = <<<EOT
<?php
/**
 * @author: $nick
 * @date: $date
 */
use Test\Lib\TestCase;
class $actionName extends $extendClass
{
    private \$_action;

    public function setUp()
    {
        \$GLOBALS['group'] = '$group';
        \$this->_action = new $apiAction();
    }
}
EOT;
        fwrite($myfile, $code);
        fclose($myfile);
        echo "............ " . $actionName . " was created!!!\n";
    }

    private function initModelTest($file, $modelName, $extendClass)
    {
        $date = date('Y/m/d');
        $nick = $this->getNick();
        $myfile = fopen($file, "w");
        $code = <<<EOT
<?php
/**
 * @author: $nick
 * @date: $date
 */
use Test\Lib\TestCase;
class $modelName extends $extendClass
{

}
EOT;
        fwrite($myfile, $code);
        fclose($myfile);
        echo "............ " . $modelName . " was created!!!\n";
    }

}

