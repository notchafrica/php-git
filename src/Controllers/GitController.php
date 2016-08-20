<?php namespace DavinBao\PhpGit\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use DavinBao\PhpGit\Git;
use Illuminate\Http\Request;

/**
 * Class GitController
 * @package DavinBao\PhpGit\Controllers
 *
 * @author davin.bao
 * @since 2016.8.18
 */
class GitController extends BaseController
{
    public $repo;
   
    public function index(Request $request)
    {
        return view('php_git::index');
    }

    public function getRepoList(Request $request){
        $repoList = app('config')->get('phpgit.repo_list');
        $currentRepo = $request->get('repo', current($repoList));
        $this->getRepo($request)->fetch();

        return new JsonResponse(array_merge(['rows'=>$repoList, 'current'=> $currentRepo], ['msg'=>'', 'code'=>200]), 200, $headers = [], 0);
    }

    public function getBranches(Request $request){
        $branchList = $this->getRepo($request)->list_branches(true);
        $status = $this->getRepo($request)->status(true);

        return new JsonResponse(array_merge(['rows'=>$branchList, 'status' => $status], ['msg'=>'', 'code'=>200]), 200, $headers = [], 0);
    }

    public function postCheckout(Request $request){
        $branch = $request->get('branch', 'master');
        $result = $this->getRepo($request)->checkout($branch);

        $commands = app('config')->get('phpgit.command');
        foreach($commands as $command){
            $this->getRepo($request)->run_command($command);
        }

        return new JsonResponse(['msg'=>$result, 'code'=>200], 200, $headers = [], 0);
    }

    public function postDelete(Request $request){
        $branch = $request->get('branch', '');
        $result = $this->getRepo($request)->delete_branch($branch);

        return new JsonResponse(['msg'=>$result, 'code'=>200], 200, $headers = [], 0);
    }

    private function getRepo(Request $request){
        if(is_null($this->repo)){
            $repoList = app('config')->get('phpgit.repo_list');
            $currentRepo = $request->get('repo', current($repoList));
            $this->repo = Git::open($currentRepo);
        }
        return $this->repo;
    }
}