<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/22
 * Time: 10:27
 */

namespace app\api\controller;


class GenerateImage
{
        public function htmlConvertPdf(){
//            $html = input('html_code');

                $html = $this->getHtml();
                $path =PDF;
                RETURN $path;

        }

    public function getHtml(){
      return  $html = '{include file="public/header" /}
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <!-- Panel Other -->
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>区域列表</h5>
        </div>
        <div class="ibox-content">
            <!--搜索框开始-->           
            <div class="row">
                <div class="col-sm-12">   
                <!--<div  class="col-sm-2" style="width: 100px">-->
                    <!--&lt;!&ndash;<div class="input-group" >  &ndash;&gt;-->
                        <!--&lt;!&ndash;<a href="{:url(\'add_article\')}"><button class="btn btn-outline btn-primary" type="button">添加</button></a>&ndash;&gt;-->
                    <!--&lt;!&ndash;</div>&ndash;&gt;-->
                <!--</div>                                            -->
                    <form name="admin_list_sea" class="form-search" method="post" action="{:url(\'index\')}">
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="text" id="key" class="form-control" name="key" value="{$val}" placeholder="输入需查询的区域名称" />
                                <span class="input-group-btn"> 
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button> 
                                </span>
                            </div>
                        </div>
                    </form>                         
                </div>
            </div>
            <!--搜索框结束-->
            <div class="hr-line-dashed"></div>
            <div class="example-wrap">
                <div class="example">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr class="long-tr">
                                <th width="3%">ID</th>
                                <th width="15%">标题</th>
                                <th width="5%">用户</th>
                                <th width="5%">区域</th>
                                <th width="4%">价格</th>
                                <th width="4%">置顶状态</th>
                                <th width="4%">电话</th>
                                <th width="10%">创建时间</th>
                                <th width="10%">更新时间</th>
                                <th width="5%">状态</th>
                                <!--<th width="10%">操作</th>-->
                            </tr>
                        </thead>
                        <script id="list-template" type="text/html">
                            {volist name="lists" id="data"}
                                <tr class="long-td">
                                    <td>{$data.id}</td>
                                    <td>{$data.title}</td>
                                    <td>{$data.user_id}</td>
                                    <td>{$data.region}</td>
                                    <td>{$data.price}</td>
                                    <td>{$data.sticky_status}</td>
                                    <td>{$data.phone}</td>
                                    <td>{$data.create_time}</td>
                                    <td>{$data.update_time}</td>
                                    <td>
                                        {if condition="$data.status==0"}
                                            <a href="javascript:;" onclick="used_state({$data.id});">
                                                <div id="zt{$data.id}"><span class="label label-info">已通过</span></div>
                                            </a>
                                        {else /}
                                            <a href="javascript:;" onclick="used_state({$data.id});">
                                                <div id="zt{$data.id}"><span class="label label-danger">未通过</span></div>
                                            </a>
                                        {/if}
                                    </td>
                                    <!--<td>-->
                                        <!--<a href="javascript:;" onclick="edit_article({$data.id})" class="btn btn-primary btn-xs btn-outline">-->
                                            <!--<i class="fa fa-paste"></i> 编辑</a>&nbsp;&nbsp;-->
                                        <!--<a href="javascript:;" onclick="del_article({$data.id})" class="btn btn-danger btn-xs btn-outline">-->
                                            <!--<i class="fa fa-trash-o"></i> 删除</a>-->
                                    <!--</td>-->
                                </tr>
                            {/volist}
                        </script>
                        <tbody id="list-content"></tbody>
                    </table>
                    <div id="AjaxPage" style="text-align:right;"></div>
                    <div style="text-align: right;">
                        共{$count}条数据，<span id="allpage"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- 加载动画 -->
<div class="spiner-example">
    <div class="sk-spinner sk-spinner-three-bounce">
        <div class="sk-bounce1"></div>
        <div class="sk-bounce2"></div>
        <div class="sk-bounce3"></div>
    </div>
</div>

{include file="public/footer" /}

<script type="text/javascript">
   
    /**
     * [Ajaxpage laypage分页]
     * @param {[type]} curr [当前页]
     */
    Ajaxpage();

    function Ajaxpage(curr){
        var key=$(\'#key\').val();
        $.getJSON(\'{:url("article/index")}\', {
            page: curr || 1,key:key
        }, function(data){      //data是后台返回过来的JSON数据
			$(".spiner-example").css(\'display\',\'none\'); //数据加载完关闭动画
            if(data==\'\'){
                $("#list-content").html(\'<td colspan="20" style="padding-top:10px;padding-bottom:10px;font-size:16px;text-align:center">暂无数据</td>\');
            }else{
                var tpl = document.getElementById(\'list-template\').innerHTML;
                laytpl(tpl).render(data, function(html){
                    document.getElementById(\'list-content\').innerHTML = html;
                });
                laypage({
                    cont: $(\'#AjaxPage\'),//容器。值支持id名、原生dom对象，jquery对象,
                    pages:\'{$allpage}\',//总页数
                    skip: true,//是否开启跳页
                    skin: \'#1AB5B7\',//分页组件颜色
                    curr: curr || 1,
                    groups: 3,//连续显示分页数
                    jump: function(obj, first){
                        if(!first){
                            Ajaxpage(obj.curr)
                        }
                        $(\'#allpage\').html(\'第\'+ obj.curr +\'页，共\'+ obj.pages +\'页\');
                    }
                });
            }
        });
    }
 

//编辑文章
function edit_article(id){
    location.href = \'./edit_article/id/\'+id+\'.html\';
}

//删除文章
function del_article(id){
    lunhui.confirm(id,\'{:url("del_article")}\');
}

//审核状态
function used_state(id){
    lunhui.status(id,\'{:url("used_state")}\',\'已审核\',\'未审核\');
}

</script>
</body>
</html>';
    }
}





