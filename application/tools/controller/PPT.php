<?php
namespace app\tools\controller;
use PhpOffice\PhpPresentation\PhpPresentation;
use think\Controller;
use app\tools\controller\Tools;

require EXTEND_PATH . 'PhpOffice/PhpPresentation/src/PhpPresentation/Autoloader.php';
\PhpOffice\PhpPresentation\Autoloader::register();
require EXTEND_PATH . 'PhpOffice/Common/src/Common/Autoloader.php';
\PhpOffice\Common\Autoloader::register();

class PPT extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

}
