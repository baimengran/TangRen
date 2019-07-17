<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        //return ROOT_PATH;
        return$_SERVER['SERVER_NAME'];
        return view();
    }
	
	public function test() {
		return view('test');	
	}


}
