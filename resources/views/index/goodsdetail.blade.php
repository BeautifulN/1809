<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<h1>商品详情</h1>
<hr/><hr/>
<table border=1 >
    <tr>
        <td>ID</td>
        <td>商品名称</td>
        <td>商品价格</td>
        <td>操作</td>
    </tr>
    @foreach ($arr as $v)
        <tr>
            <td>{{ $v->goods_id }}</td>
            <td><a href="goodsdetail?goods_id={{ $v->goods_id }}">{{ $v->goods_name }}</a></td>
            <td>{{ $v->goods_selfprice }}</td>
            <td>
                [<a href="add/{{ $v->goods_id }}" class="del">加入购物车</a>]
                {{--[<a href="javascript:;" class="update">修改</a>]--}}
            </td>

        </tr>
    @endforeach
</table>
<hr/><hr/><hr/>
<h3>浏览历史</h3>
<hr/>
<table border=2 >
    <tr>
        <td>ID</td>
        <td>商品名称</td>
        <td>商品价格</td>
        <td>浏览次数</td>
    </tr>
    @foreach ($info as $k=>$v)
        <tr>
            <td>{{ $v['goods_id'] }}</td>
            <td>{{ $v['goods_name'] }}</td>
            <td>{{ $v['goods_selfprice'] }}</td>
            <td>{{ $v['goods_key'] }}</td>
        </tr>
    @endforeach
</table>
<hr/><hr/><hr/>
<h3>浏览记录</h3>
<hr/>
<table border=2 >
    <tr>
        <td>ID</td>
        <td>商品名称</td>
        <td>商品价格</td>
        <td>浏览次数</td>
    </tr>
    @foreach ($data as $k=>$v)
        <tr>
            <td>{{ $v['goods_id'] }}</td>
            <td>{{ $v['goods_name'] }}</td>
            <td>{{ $v['goods_selfprice'] }}</td>
            <td>{{ $v['goods_key'] }}</td>
        </tr>
    @endforeach
</table>
<hr/>
<hr/>
<div id="qrcode" type="center">

</div>

</body>
</html>
<script src="/js/jquery/jquery-1.12.4.min.js"></script>
<script src="/js/weixin/qrcode.js"></script>
<script type="text/javascript">
    new QRCode(document.getElementById("qrcode"), "{{$code_url}}");
</script>