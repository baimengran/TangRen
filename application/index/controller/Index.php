<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return view();
    }
	
	public function test() {
		return view('test');	
	}


}
