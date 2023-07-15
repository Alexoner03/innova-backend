<html>
<head>
    <style>
       .logo {
           text-align: center;
       }
       .logo img {
           width: 200px;
           margin-bottom: 1rem;
       }
       .logo .title {
           font-weight: bold;
           font-size: 18px;
       }
       .logo p {
           margin: 0 0 .2rem;
           font-size: 14px;
       }
       *, body, .serie {
           font-family: sans-serif;
       }
       hr {
           border: 1px solid black;
       }


       .serie {
           text-align: center;
           font-size: 16px;
           font-weight: bold;
       }

       .serie p {
           margin: 0 0 .2rem;
       }


       .detalle th {
           font-weight: normal !important;
       }

       .info, .totales, .detalle, .resumen{
           width: 90% !important;
           margin: auto;
           margin-bottom: 1rem;
       }
       .info p {
           margin: 0 0 .2rem;
           font-size: 14px;
       }

       .info p span {
           font-weight: bold;
       }


       .totales p {
           font-weight: bold;
           margin: 0 0.2rem;
           text-align: right;
           font-size: 14px;
       }

       .resumen p {
           margin: 0 .2rem;
           font-size: 14px;
       }

       .resumen p  span{
           font-weight: bold;
       }

       .qr_show {
           text-align: center;
       }

    </style>
</head>
<body>
    <div class="logo">
        <img src="{{$logo}}" alt="logo">
        <p class="title">{{$config["razon1"]}}</p>
        <p>
            {{$config["direccion1"]}}
        </p>
        <p>
            {{$config["ruc1"]}}
        </p>
        <p>
           {{$config["datos1"]}}
        </p>
    </div>

        <hr>
        <div class="serie">
            <p>
                {{str($model->documento)->replace(" 2", "")}}
            </p>
            @if(!str($model->documento)->contains("PEDIDO"))
            <p>
                {{ str($model->documento)->contains("BOLETA") ? "B" : "F"}}{{$config["serie1"]."-".$model->serieventas}}
            </p>
            @endif
        </div>
        <hr>

    <div class="info">
        <p>
            <span>FECHA:</span>
            {{$model->fecha}}
            <span style="float: right">
                <strong>HORA:</strong> <strong style="font-weight: normal">{{$model->hora}}</strong>
            </span>
        </p>
        @if(!str($model->documento)->contains("PEDIDO"))
            <p>
                <span>{{str($model->documento)->contains("FACTURA") ? "RUC:" : "DNI:"}}</span>
                {{$model->ruc}}
            </p>
        @else()
            <p>
                <span>SERIE</span>
                {{$model->serieventas}}
            </p>
        @endif
        <p>
            <span>CLIENTE:</span>
            {{$model->cliente}}
        </p>
        <p>
            <span>DIRECCIÓN:</span>
            {{$model->direccion}}
        </p>
        <p>
            <span>VENDEDOR:</span>
            {{$model->vendedor}}
        </p>
        @if(!str($model->documento)->contains("PEDIDO"))
            <p>
                <span>FORMA PAGO:</span>
                {{$model->credito === "CONTADO" ? "CONTADO": "CREDITO"}}
            </p>
        @endif
    </div>

    <div class="detalle">
        <table style="width: 100% !important;">
            <thead>
            <tr style="background-color: black; color: white; font-size: 14px;">
                <th>CAN</th>
                <th style="width: 100%">PRODUCTO</th>
                <th style="padding: 0 .5rem; width: 60px; text-align: center">UNIT</th>
                <th style="padding: 0 .5rem; width: 60px; text-align: center">IMP</th>
            </tr>
            </thead>
            <tbody>

            @foreach($registers as $register)
                <tr style="font-size: 13px; text-align: center">
                    <td>{{$register->cantidad}}</td>
                    <td style="text-align: left">{{$register->producto}}</td>
                    <td>S/. {{number_format($register->unitario, 2, '.', '')}}</td>
                    <td>S/. {{number_format($register->importe, 2, '.', '')}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="totales">
        @if(str($model->documento)->contains("FACTURA"))
            <p>OP. GRAVADAS: S/. {{number_format(($total - ($total * 0.18)), 2, '.', '')}}</p>
            <p>IGV: S/. {{number_format(($total * 0.18), 2, '.', '')}}</p>
        @endif
        <p>TOTAL: S/. {{number_format($total, 2, '.', '')}} </p>
        @if(!str($model->documento)->contains("PEDIDO"))
            <p>ICBPER: S/. 0.00</p>
            <p>IMPORTE TOTAL: S/. {{number_format($total, 2, '.', '')}}</p>
        @endif
    </div>


    <div class="resumen">
        <p><span>SON:</span> {{$resumen}}</p>
    </div>

       <div class="qr_show">
           @if(!str($model->documento)->contains("PEDIDO"))
           <img
               src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate($qrdata)) !!} ">
           @endif
           <p style="font-weight: bold">GRACIAS POR SU PREFERENCIA</p>
       </div>
</body>
</html>
