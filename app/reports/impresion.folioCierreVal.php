<?php
 session_start();
 require_once('../fpdf/fpdf.php');
 $folio = $_SESSION['folio'];
 $Cabecera = $_SESSION['cabecera'];
 $Detalle = $_SESSION['detalle'];

foreach ($Cabecera as $data):
        foreach ($Detalle as $key2) {
          $FCV= $key2->FECHA_CIERRE_VAL;
        }
        foreach ($Cabecera as $key ) {
          $tp_tes = $key->TP_TES;
          $pago_tes = $key->PAGO_TES;
        }
      
        $pdf = new FPDF('P','mm','Letter');
        $pdf->AddPage();
        $pdf->Image('../views/images/headerVacio.jpg',10,15,205,55);
        $pdf->SetFont('Courier', 'B', 25);
        $pdf->SetTextColor(255,0,0);
        $pdf->SetXY(110, 28);
        $pdf->Write(10,'Validacion');
        $pdf->SetXY(110, 38);
        $pdf->Write(10,utf8_decode('de Recepcion'));
        $pdf->Ln(10);
        $pdf->SetTextColor(0,0,0);
        $pdf->Ln(65);
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetXY(60, 60);
      $pdf->Write(6, ''); // Control de impresiones.
      $pdf->Ln();
      $pdf->Write(6,'Folio de Pago: '.$tp_tes.' -->Monto del pago: $ '.number_format($pago_tes,2).'Folio Validacion: '.$folio);
      $pdf->Ln();

        foreach ($Cabecera as $data){
        $pdf->SetFont('Arial', 'B', 9);
      $folio = $data->CVE_DOC;
      $pdf->Write(6,'Orden de Compra No:'.$data->CVE_DOC);
      $pdf->Ln();
      $pdf->Write(6,'Fecha de Elaboracion OC: '.$data->FECHAELAB);
      $pdf->Ln();
      $pdf->Write(6,'Fecha Recepcion: '.$FCV.' Folio Recepcion: '.$folio );
      $pdf->Ln();
      $pdf->Write(6,'Usuario Recepcion : '.$data->USUARIO_RECIBE );
      $pdf->Ln();
      $pdf->Write(6,'Proveedor : ('.$data->CVE_CLPV.')'.$data->NOMBRE.', RFC: '.$data->RFC);
      $pdf->Ln();
      $pdf->Write(6,'Direccion: Calle :'.$data->CALLE.', Num Ext:'.$data->NUMEXT.', Colonia: '.$data->COLONIA);
      $pdf->Ln();
      $pdf->Write(6,'Estado: '.$data->ESTADO.', Pais: '.$data->CVE_PAIS);
      $pdf->Ln();
      $pdf->Write(6,'Dias de Credito: '.$data->DIASCRED.'     ########    Cuenta de pago: '.$data->CUENTA);
      $pdf->Ln();
      $pdf->Write(6,'Acepta Efectivo: '.$data->TP_EFECTIVO.'   ### Acepta Transferencia: '.$data->TP_TRANSFERENCIA.'   ###   Acepta Credito: '.$data->TP_CREDITO.'    ####   Acepta Cheque: '.$data->TP_CHEQUE);
      $pdf->Ln();

      $pdf->Ln();
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(8,6,"Part.",1);
        $pdf->Cell(65,6,"Descripcion",1);
        $pdf->Cell(10,6,"Cant",1);
        $pdf->Cell(8,6,"UM",1);
        $pdf->Cell(18,6,"Precio Val",1);
        $pdf->Cell(14,6,"Desc 1",1);
        $pdf->Cell(14,6,"Desc 2",1);
        $pdf->Cell(14,6,"Desc 3",1);
        $pdf->Cell(18,6,"Subtotal",1);
        $pdf->Cell(15,6,"Iva",1);
        $pdf->Cell(15,6,"Total",1);
        $pdf->Ln();
        $pdf->SetFont('Arial', 'I', 7);
          $descuento = 0;
            $subtotal = 0;
            $iva = 0;
            $total = 0;
            $partida= 0;
            $desctotal=0;
        foreach($Detalle as $row){
          $subtotal += ($row->PRECIO_LISTA * $row->CANTIDAD_REC);
          $desclinea = ($row->DESC1_M + $row->DESC2_M + $row->DESC3_M)*$row->CANTIDAD_REC;
          $desctotal += ($row->DESC1_M + $row->DESC2_M + $row->DESC3_M)*$row->CANTIDAD_REC; 
            $pdf->Cell(8,6,($row->PARTIDA),'L,T,R');
            $pdf->Cell(65,6,substr($row->NOMPROD, 0,45), 'L,T,R');
            $pdf->Cell(10,6,number_format($row->CANTIDAD_REC,2),'L,T,R');
            $pdf->Cell(8,6,$row->UM,'L,T,R');
            $pdf->Cell(18,6,'$ '.number_format($row->PRECIO_LISTA,2),'L,T,R');
            $pdf->Cell(14,6,'$ '.number_format($row->DESC1_M,2),'L,T,R');
            $pdf->Cell(14,6,'$ '.number_format($row->DESC2_M,2),'L,T,R');
            $pdf->Cell(14,6,'$ '.number_format($row->DESC3_M,2),'L,T,R');
            $pdf->Cell(18,6,'$ '.number_format(($row->PRECIO_LISTA * $row->CANTIDAD_REC)-$desclinea ,2),'L,T,R');
            $pdf->Cell(15,6,'$ '.number_format( (($row->PRECIO_LISTA * $row->CANTIDAD_REC) - $desclinea)* .16,2),'L,T,R');
            $pdf->Cell(15,6,'$ '.number_format( (($row->PRECIO_LISTA * $row->CANTIDAD_REC) - $desclinea)* 1.16,2),'L,T,R');
            $pdf->Ln(4);        
            $pdf->Cell(8,6,"",'L,R');
            $pdf->Cell(65,6,substr($row->NOMPROD, 45 , 50),'L,R');
            $pdf->Cell(10,6,"",'L,R');
            $pdf->Cell(8,6,"",'L,R');
            $pdf->Cell(18,6,"",'L,R');
            $pdf->Cell(14,6,"% ".number_format($row->DESC1,2),'L,R');
            $pdf->Cell(14,6,"% ".number_format($row->DESC2,2),'L,R');
            $pdf->Cell(14,6,"% ".number_format($row->DESC3,2),'L,R');
            $pdf->Cell(18,6,"",'L,R');
            $pdf->Cell(15,6,"",'L,R');
            $pdf->Cell(15,6,"",'L,R');
            $pdf->Ln(4);
            $pdf->Cell(8,6,"",'L,B,R');
            $pdf->Cell(65,6,($row->PROD),'L,B,R');
            $pdf->Cell(10,6,"",'L,B,R');
            $pdf->Cell(8,6,"",'L,B,R');
            $pdf->Cell(18,6,"",'L,B,R');
            $pdf->Cell(14,6,"",'L,B,R');
            $pdf->Cell(14,6,"",'L,B,R');
            $pdf->Cell(14,6,"",'L,B,R');
            $pdf->Cell(18,6,"",'L,B,R');
            $pdf->Cell(15,6,"",'L,B,R');
            $pdf->Cell(15,6,"",'L,B,R');
            $pdf->Ln(6);

        }
          $pdf->Cell(8,6,"",0);
          $pdf->Cell(65,6,"",0);
          $pdf->Cell(10,6,"",0);
          $pdf->Cell(8,6,"",0);
          $pdf->Cell(18,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(18,6,"",0);
          $pdf->Cell(15,6,"SubTotal",1);
          $pdf->Cell(15,6,'$ '.number_format($subtotal,2),1);
          $pdf->Cell(15,6,"",0);
          $pdf->Ln();
          $pdf->Cell(8,6,"",0);
          $pdf->Cell(65,6,"",0);
          $pdf->Cell(10,6,"",0);
          $pdf->Cell(8,6,"",0);
          $pdf->Cell(18,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(18,6,"",0);
          $pdf->Cell(15,6,"Descuento",1);
          $pdf->Cell(15,6,'$ '.number_format($desctotal,2),1);
          $pdf->Ln();
          $pdf->Cell(8,6,"",0);
          $pdf->Cell(65,6,"",0);
          $pdf->Cell(10,6,"",0);
          $pdf->Cell(8,6,"",0);
          $pdf->Cell(18,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(18,6,"",0);
          $pdf->Cell(15,6,"IVA",1);
          $pdf->Cell(15,6,'$ '.number_format(($subtotal - $desctotal) * .16,2),1);
          $pdf->Ln();
          $pdf->Cell(8,6,"",0);
          $pdf->Cell(65,6,"",0);
          $pdf->Cell(10,6,"",0);
          $pdf->Cell(8,6,"",0);
          $pdf->Cell(18,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(14,6,"",0);
          $pdf->Cell(18,6,"",0);
          $pdf->Cell(15,6,"Total",1);
          $pdf->Cell(15,6,'$ '.number_format(($subtotal - $desctotal) * 1.16,2),1);
          $pdf->Ln();
          $pdf->Output('Pre_On_1.'.$folio.'_.pdf','i');
  endforeach;