<?php
/**
 * @author: yidao
 * @date: 2017/5/25
 */
define('APP_PATH', dirname(dirname(__DIR__)) . '/');
define('LIB_PATH', dirname(__DIR__) . '/');

class MicroService {
    private $baseActionArr = [
        'Home'=>'APICommonAction',
        'Car'=>'APICarCommonAction',
        'Admin'=>'AdminCommonAction',
        'Hotel'=>'APIHotelCommonAction',
        'Mainadmin'=>'APIMainAdminCommonAction',
        'Shop'=>'APIShopCommonAction',
        'Shopadmin'=>'APIShopAdminCommonAction',
        'Web'=>'WebCommonAction'
    ];
    function init($dir, $model)
    {
        $this->initCreate($dir, $model, 'action');
        $this->initCreate($dir, $model, 'model');
    }

    /**
     * @see MicroService::initAction
     * @see MicroService::initModel
     */
    private function initCreate($dir, $model, $type)
    {
        $func = "init" . ucfirst($type);
        switch ($type) {
            case 'action':
                $className = 'API' . $model . 'Action';
                $file = LIB_PATH . 'Action/' . $dir . '/' . $className . '.class.php';
                if(!array_key_exists($dir, $this->baseActionArr)){
                    echo 'Action dir:'.$dir.' isn\'t exsit, retry:' . "\n";
                    $dir = ucfirst(trim(fgets(STDIN)));
                    return $this->init($dir, $model);
                }
                $extendClass = $this->baseActionArr[$dir];
                break;
            case 'model':
                $extendClass = 'BaseModel';
                $className = $model . 'Model';
                $file = LIB_PATH . 'Model/' . $className . '.class.php';
                break;
        }
        if (file_exists($file)) {
            echo $file . ' is exist! Override it? Y/N' . "\n";
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
        echo $file . "created!\n";
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
    }

}

echo "color c ".'Enter table name:' . "\n";
$table = trim(fgets(STDIN));
$model = implode("", array_map("ucfirst", explode('_', $table)));
echo 'Chose action dir:' . "\n";
$dir = ucfirst(trim(fgets(STDIN)));
$c = new MicroService();
$c->init($dir, $model);