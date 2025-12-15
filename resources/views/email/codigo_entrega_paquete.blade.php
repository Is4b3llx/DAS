<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Código de validación</title>
</head>
<body style="margin:0;padding:24px;background-color:#f2f5f9;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#1f2933;">
    <div style="max-width:620px;margin:0 auto;">
        <div style="background:#ffffff;border-radius:14px;box-shadow:0 8px 24px rgba(15,35,95,0.10);border:1px solid #e5e9f2;overflow:hidden;">

            <div style="background:linear-gradient(120deg,#00a4c7,#00749b);padding:22px 28px;color:#ffffff;">
                @php
                    $codigoSeguimiento = optional($paquete->solicitud)->codigo_seguimiento ?? $paquete->codigo;
                @endphp
                <h2 style="margin:0;font-size:20px;font-weight:700;">
                    Código de validación para entrega de tu paquete {{ $codigoSeguimiento }}
                </h2> 
            </div>

            <div style="padding:24px 28px 10px;">
                @php
                    $soli = optional(optional($paquete->solicitud)->solicitante);
                    $nombre = trim(($soli->nombre ?? '').' '.($soli->apellido ?? ''));
                    $codigoSeguimiento = optional($paquete->solicitud)->codigo_seguimiento ?? $paquete->codigo;
                @endphp

                <p>Hola {{ $nombre ?: 'estimado/a' }},</p>

                <p>
                    Tu paquete con código de seguimiento
                    <strong>{{ $codigoSeguimiento }}</strong> está listo para ser entregado.
                </p>

                <p>
                    <strong>Este código será utilizado por el voluntario a cargo para validar tu identidad y la entrega del paquete.</strong>
                </p>

                <p>
                    En caso de tener un contacto de referencia en el lugar, debes indicarle el código para que el voluntario a cargo haga la entrega.
                    <br>
                    <strong>Caso contrario, NO SE ENTREGARÁ EL PAQUETE.</strong>
                </p>
                <h2 style="font-size: 20px; font-weight: bold; letter-spacing: 4px; text-align:center; margin: 16px 0;">
                    {{ $codigo }}
                </h2>

                <p>Gracias por tu colaboración.</p>
            </div>

            <div style="padding:16px 24px;background:#f7fafc;border-top:1px solid #e5e9f2;font-size:12px;color:#61718a;">
                <p style="margin:0;">Este mensaje fue enviado por DAS - Alas Chiquitanas.</p>
            </div>
        </div>
    </div>
</body>
</html>
