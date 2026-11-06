<?php

namespace App\Http\Controllers\Soap;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LibrarySoapController extends Controller
{
    /**
     * GET /soap/sipus.wsdl
     */
    public function wsdl(): Response
    {
        $endpointUrl = url('/soap/sipus');

        $wsdlXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="LibrarySoapService"
  targetNamespace="http://sipus.del.ac.id/soap/sipus"
  xmlns:tns="http://sipus.del.ac.id/soap/sipus"
  xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
  xmlns="http://schemas.xmlsoap.org/wsdl/">
  <message name="CheckClearanceStatusRequest">
    <part name="nim_peminjam" type="xsd:string"/>
  </message>
  <message name="CheckClearanceStatusResponse">
    <part name="active_loans" type="xsd:int"/>
    <part name="is_cleared" type="xsd:boolean"/>
  </message>
  <portType name="LibraryPortType">
    <operation name="CheckClearanceStatus">
      <input message="tns:CheckClearanceStatusRequest"/>
      <output message="tns:CheckClearanceStatusResponse"/>
    </operation>
  </portType>
  <binding name="LibrarySoapBinding" type="tns:LibraryPortType">
    <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="CheckClearanceStatus">
      <soap:operation soapAction="http://sipus.del.ac.id/soap/sipus#CheckClearanceStatus"/>
      <input><soap:body use="literal" namespace="http://sipus.del.ac.id/soap/sipus"/></input>
      <output><soap:body use="literal" namespace="http://sipus.del.ac.id/soap/sipus"/></output>
    </operation>
  </binding>
  <service name="LibrarySoapService">
    <port name="LibraryPort" binding="tns:LibrarySoapBinding">
      <soap:address location="{$endpointUrl}"/>
    </port>
  </service>
</definitions>
XML;

        return response($wsdlXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * POST /soap/sipus
     */
    public function handle(Request $request): Response
    {
        $rawXml = trim($request->getContent());

        if ($rawXml === '') {
            return $this->soapFault('soap:Client', 'Body XML SOAP tidak boleh kosong.');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($rawXml);

        if ($xml === false) {
            return $this->soapFault('soap:Client', 'Format XML tidak valid (Malformed XML).');
        }

        $nodes = $xml->xpath('//*[local-name()="nim_peminjam"]');
        $nim = isset($nodes[0]) ? trim((string) $nodes[0]) : '';

        if ($nim === '') {
            return $this->soapFault('soap:Client', 'Elemen <nim_peminjam> kosong atau tidak ditemukan.');
        }

        // Hitung buku yang masih berstatus dipinjam oleh NIM tersebut
        $activeLoans = Peminjaman::where('nim_peminjam', $nim)
            ->where('status', 'dipinjam')
            ->count();

        $isCleared = $activeLoans === 0 ? 'true' : 'false';
        $safeNim = htmlspecialchars($nim, ENT_XML1, 'UTF-8');

        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
  xmlns:m="http://sipus.del.ac.id/soap/sipus">
  <soap:Body>
    <m:CheckClearanceStatusResponse>
      <m:nim_peminjam>{$safeNim}</m:nim_peminjam>
      <m:active_loans>{$activeLoans}</m:active_loans>
      <m:is_cleared>{$isCleared}</m:is_cleared>
    </m:CheckClearanceStatusResponse>
  </soap:Body>
</soap:Envelope>
XML;

        return response($responseXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * Helper pembentuk <soap:Fault>
     */
    private function soapFault(string $faultCode, string $faultMessage): Response
    {
        $safeMsg = htmlspecialchars($faultMessage, ENT_XML1, 'UTF-8');

        $faultXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <soap:Fault>
      <faultcode>{$faultCode}</faultcode>
      <faultstring>{$safeMsg}</faultstring>
    </soap:Fault>
  </soap:Body>
</soap:Envelope>
XML;

        return response($faultXml, 500)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}