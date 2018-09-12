<?php
namespace app\tools\controller;
use PhpOffice\PhpPresentation\PhpPresentation;
use think\Controller;
use app\tools\controller\Tools;

require EXTEND_PATH . 'PhpOffice/PhpPresentation/src/PhpPresentation/Autoloader.php';
\PhpOffice\PhpPresentation\Autoloader::register();
require EXTEND_PATH . 'PhpOffice/Common/src/Common/Autoloader.php';
\PhpOffice\Common\Autoloader::register();
require EXTEND_PATH.'PhpOffice/PhpPresentation/samples/Sample_Header.php';

use \PhpOffice\PhpPresentation\IOFactory;
use \PhpOffice\PhpPresentation\Slide;
use \PhpOffice\PhpPresentation\Shape\RichText;

/**
 * ppt处理类 暂时没用
 * Class Powerpoint
 * @package app\tools\controller
 */
class Powerpoint extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        set_time_limit(10);

        $pptReader = IOFactory::createReader('PowerPoint2007');
        $oPHPPresentation = $pptReader->load(EXTEND_PATH.'PhpOffice/PhpPresentation/samples/resources/Sample_12.pptx');
        //PhpPptTree
        $oTree = new \PhpPptTree($oPHPPresentation);
        echo $oTree->display();
        if (!CLI) {
            //include_once 'Sample_Footer.php';
            require EXTEND_PATH.'PhpOffice/PhpPresentation/samples/Sample_Footer.php';
        }
    }


}
