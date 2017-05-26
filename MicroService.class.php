<?php
/**
 * ThinkPhp widget
 * @author: yidao
 * @date: 2017/5/25
 */
define('APP_PATH', dirname(__DIR__) . '/');
define('LIB_PATH', APP_PATH . 'Lib/');
define('TEST_PATH', APP_PATH . 'Test/');
$superAction = ['Home' => 'APICommonAction', 'Car' => 'APICarCommonAction', 'Admin' => 'AdminCommonAction', 'Hotel' => 'APIHotelCommonAction', 'Mainadmin' => 'APIMainAdminCommonAction', 'Shop' => 'APIShopCommonAction', 'Shopadmin' => 'APIShopAdminCommonAction', 'Web' => 'WebCommonAction'];
$service = new MicroService($superAction);
$service->run();

class MicroService {

    private $baseActionArr;

    public function __construct($superAction)
    {
        $this->baseActionArr = $superAction;
    }

    public function run()
    {
        echo "\nApiBuilder 1.0, create api with ThinkPHP." . "\n";
        echo "\n--------------- Now, we begin to create Action|Model|UnitTest! ---------------\n\n";
        echo 'Enter table name:' . "\n";
        $table = trim(fgets(STDIN));
        $model = implode("", array_map("ucfirst", explode('_', $table)));
        $dir = "";
        while ($dir == "" || !array_key_exists($dir, $this->baseActionArr)) {
            echo 'Select action in list: [' . implode(',', array_keys($this->baseActionArr)) . ']:' . "\n";
            $dir = ucfirst(trim(fgets(STDIN)));
        }
        $this->init($dir, $model);
    }

    function init($dir, $model)
    {
        echo "\n\nApiBuilder RESULTS:\n\n";
        $this->initCreate($dir, $model, 'action');
        $this->initCreate($dir, $model, 'model');
        $this->initCreate($dir, $model, 'actionTest');
        $this->initCreate($dir, $model, 'modelTest');
    }


    private function initCreate($dir, $model, $type)
    {
        /**
         * @see MicroService::initAction
         * @see MicroService::initModel
         * @see MicroService::initActionTest
         * @see MicroService::initModelTest
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
                    $extendClass = 'TestCase';
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
            echo "\n" .$file . ' is exist!' . "\n\n" . "Cover it, Y/N ?\n";
            $answer = strtolower(trim(fgets(STDIN)));
            if ($answer == 'y') {
                $this->{$func}($file, $className, $extendClass);
            } else {
                return;
            }
        } else {
            $this->{$func}($file, $className, $extendClass);
        }
    }

    private function initAction($file, $actionName, $extendClass)
    {
        $myfile = fopen($file, "w");
        $code = <<<EOT
<?php
/**
 * @author microService
 */
class  $actionName extends $extendClass
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
        echo "............ ".$doc[$extendClass] . '/' . $actionName . " was created!!!\n";
    }

    private function initModel($file, $modelName, $extendClass)
    {
        $myfile = fopen($file, "w");
        $code = <<<EOT
<?php
/**
 * @author microService
 */
class  $modelName extends $extendClass
{

}
EOT;
        fwrite($myfile, $code);
        fclose($myfile);
        echo "............ ".$modelName . " was created!!!\n";
    }

    private function initActionTest($file, $actionName, $extendClass)
    {
        $apiAction = substr($actionName, 0, -4);
        $myfile = fopen($file, "w");
        $code = <<<EOT
<?php
/**
 * @author microService
 */
use Test\Lib\TestCase;
class  $actionName extends $extendClass
{
    private \$_action;

    public function setUp()
    {
        \$this->_action = new $apiAction();
    }
}
EOT;
        fwrite($myfile, $code);
        fclose($myfile);
        echo "............ ".$actionName . " was created!!!\n";
    }

    private function initModelTest($file, $modelName, $extendClass)
    {
        $myfile = fopen($file, "w");
        $code = <<<EOT
<?php
/**
 * @author microService
 */
use Test\Lib\TestCase;
class  $modelName extends $extendClass
{

}
EOT;
        fwrite($myfile, $code);
        fclose($myfile);
        echo "............ ".$modelName . " was created!!!\n";
    }
}

