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
<h1>商品展示</h1>
<hr/><hr/>
<table border=1 >
    <tr>
        <td>ID</td>
        <td>订单编号</td>
        <td>订单价格</td>
        <td>订单生成时间</td>
        <td>操作</td>
    </tr>
    @foreach ($arr as $v)
        <tr>
            <td>{{ $v->order_id }}</td>
            <td>{{ $v->order_number }}</td>
            <td>{{ $v->order_amount }}</td>
            <td>{{ $v->create_time }}</td>
            <td>
                [<a href="/test/{{ $v->order_id }}" class="del">立即支付</a>]
                {{--[<a href="javascript:;" class="update">修改</a>]--}}
            </td>

        </tr>
    @endforeach
</table>
</body>
</html>
