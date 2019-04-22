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
        <h1>购物车展示</h1>
        <hr/><hr/>
    <table border=1 >
        <tr>
            <td>商品名称</td>
            <td>商品价格</td>
            <td>商品数量</td>
        </tr>
        @foreach ($goods_list as $v)
            <tr>
                <td>{{ $v['goods_name'] }}</td>
                <td>{{ $v['goods_selfprice'] }}</td>
                <td>{{ $v['goods_num'] }}</td>
            </tr>
        @endforeach
    </table>
        总价：¥{{$total}}<br>
        <form action="create" method="get">
            <input type="submit" value="提交订单">
        </form>

</body>
</html>