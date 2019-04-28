<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>layout 后台大布局 - Layui</title>
    <link rel="stylesheet" href="/layui/css/layui.css">
</head>
<body class="layui-layout-body">
<table class="layui-table">
    <colgroup>
        <col width="150">
        <col width="200">
        <col>
    </colgroup>
    <thead>
    <tr>
        <th><input  type="checkbox" class="checkbox">全选</th>
        <th>排序</th>
        <th>用户id</th>
        <th>用户名称</th>
    </tr>
    </thead>
    <tbody>
    @foreach($res as $k=>$v)
        <tr>
            <td><input type="checkbox" class="box" openid="{{$v-> openid}}"></td>
            <td>{{$v-> id}}</td>
            <td>{{$v-> openid}}</td>
            <td>{{$v-> nickname}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="layui-form-item">
    <label class="layui-form-label">输入框</label>
    <div class="layui-input-block">
        <input type="text"  class="text" required  lay-verify="required" placeholder="请输入群发内容" autocomplete="off" class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-input-block">
        <button class="layui-btn" lay-submit lay-filter="formDemo">立即提交</button>
        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
    </div>
</div>
<script src="/js/jquery/jquery-1.12.4.min.js"></script>
<script src="/layui/layui.js"></script>
<script>
    //实例化layui
    layui.use('element', function(){
        var element = layui.element;
    });
</script>
</body>
</html>
<script> //获取复选框
    $(function(){
        //全选
        $('.checkbox').click(function(){
            var _this = $(this);
            var check = _this.prop('checked');
            $('.box').prop('checked',check);
        });
        $('.layui-btn').click(function(){
            var _val = $('.text').val();
            //    console.log(_val);

            var box = $('.box');
            var openid = "";
            box.each(function(index){
                if($(this).prop('checked') == true){
                    openid += $(this).attr('openid')+",";
                }
            })
            openid = openid.substr(0,openid.length-1);
//            console.log(openid);
            location.href="contents?openid="+openid+'&name='+_val;
        })
    })
</script>