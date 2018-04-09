<?php
declare(strict_types=1);
namespace App\Controller;
use App\Process\System\ProcessList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProcessController
 * @package App\Controller
 * @author chris westerfield <chris@mjr.one>
 */
class ProcessController extends Controller
{
    /**
     * @Route("/process", name="process_list")
     */
    public function ListAction():Response
    {
        $po = new ProcessList();
        return $this->render(
            'process.html.twig',
            [
                'processes'=>$po->getProcessList(),
            ]
        );
    }
}