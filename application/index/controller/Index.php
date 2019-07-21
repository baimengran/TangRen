<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return view('application/index/view/index/aaa.php');
    }
	
	public function test() {
		return view('test');	
	}


}
