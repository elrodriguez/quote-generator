<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CitarController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function index()
    {
        return view('citar');
    }

    public function citar(Request $request)
    {
        /////primero nos atenticamos

        $doi = $request->input('url'); 
        $is_doi=true;
        if(strpos($doi, '/')){
            $doi = str_replace("https://dx.doi.org/", "", $doi); // ES DOI
            $doi = str_replace("https://doi.org/", "", $doi); // ES DOI
            $doi = str_replace("http://dx.doi.org/", "", $doi); // ES DOI
            $doi = str_replace("http://doi.org/", "", $doi); // ES DOI
        }else{
            $doi = str_replace("-", "", $doi);
            $is_doi =false;         //es ISBN
        }
        

        $normativa = $request->input('normativa');


        $response = $this->client->request('POST', 'https://api.mendeley.com/oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => '14971',
                'client_secret' => '1ppN5HZmu5rswviU',
                'scope' => 'all'
            ]
        ]);

        $body = $response->getBody();

        $accessToken = json_decode($body)->access_token;

        /////////////luego buscamos el documento para optener el id/////////////////

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/vnd.mendeley-document.1+json'
        ];

       
        if($is_doi){
            $search_url = "https://api.mendeley.com/catalog?doi=" . urlencode($doi);
        }else{
            $search_url = "https://api.mendeley.com/catalog?isbn=" . $doi;
        }

        $response = $this->client->request('GET', $search_url, [
            'headers' => $headers
        ]);

        $document = json_decode($response->getBody()->getContents());
        
        $cita = $this->generar_cita($document, $normativa);
        
        return response()->json(['cita' => $cita]);
    }

    public function generar_cita($document, $normativa)
    {
        switch ($normativa) {
            case 'apa':
                return $this->generate_apa($document[0]);
            case 'chicago':
                return $this->generate_chicago($document[0]);
            case 'mla':
                return $this->generate_mla($document[0]);
            case 'harvard':
                return $this->generate_harvard($document[0]);
            case 'iso690':
                return $this->generate_iso690($document[0]);
            case 'ieee':
                return $this->generate_ieee($document[0]);
            case 'vancouver':
                return $this->generate_vancouver($document[0]);
            default:
                return 'Formato de cita no válido';
        }
    }

    public function generate_apa($document)
    {
        $authors = array();

        //Obtener el nombre de los autores
        foreach ($document->authors as $author) { //solo la inicial del primer nombre
            array_push($authors, $author->last_name . ", " . substr($author->first_name, 0, 1).".");
        }

        $citation = '<p>';

        //Añadir los apellidos de los autores
        if (count($authors) == 1) {
            $citation .= $authors[0] . " ";
        } elseif (count($authors) == 2) {
            $citation .= $authors[0] . " y " . $authors[1] . " ";
        } elseif (count($authors) == 3) {
            $citation .= $authors[0] . ", " . $authors[1] . ", y " . $authors[2] . " ";
        } elseif (count($authors) > 3) {
            $citation .= $authors[0] . " et al. ";
        }

        //Añadir el año de publicación y el título del artículo
        $citation .= "(" . substr($document->year, 0, 4) . "). " . $document->title . ". ";

        //Añadir el nombre de la revista
        if (isset($document->source)) {
            $citation .= "<i>" . $document->source . "</i>";
        }

        //Añadir el volumen y el número (si están disponibles)
        if (isset($document->volume)) {
            $citation .= ", " . $document->volume;
        }
        if (isset($document->issue)) {
            $citation .= "(" . $document->issue . ")";
        }

        //Añadir las páginas
        if (isset($document->pages)) {
            $citation .= ", " . $document->pages;
        }

        //Añadir el DOI
        if (isset($document->identifiers->doi)) {
            $citation .= ' <a href="https://doi.org/' . $document->identifiers->doi . '">' ."https://doi.org/".$document->identifiers->doi . '</a>';
        }

        $citation .= "</p>";

        return $citation;
    }

    public function generate_chicago($document)
    {
        $authors = array();

        //Obtener el nombre de los autores
        foreach ($document->authors as $author) {
            $name = $author->last_name . ", " . $author->first_name;
            array_push($authors, $name);
        }

        $citation = '<p>';

        //Añadir los nombres de los autores
        if (count($authors) == 1) {
            $citation .= $authors[0] . ". ";
        } elseif (count($authors) == 2) {
            $citation .= $authors[0] . " and " . $authors[1] . ". ";
        } else {
            for ($i = 0; $i < count($authors) - 1; $i++) {
                $citation .= $authors[$i] . ", ";
            }
            $citation .= "and " . $authors[count($authors) - 1] . ". ";
        }

        //Añadir el título del artículo
        $citation .= '"' . $document->title . '," ';

        //Añadir el nombre de la revista
        if (isset($document->source)) {
            $citation .= '<em>' . $document->source . '</em> ';
        }

        //Añadir el volumen
        if (isset($document->volume)) {
            $citation .= $document->volume;
        }

        //Añadir el número
        if (isset($document->issue)) {
            $citation .= ', no. ' . $document->issue;
        }

        //Añadir el año de publicación
        $citation .= ' (' . $document->year . '): ';

        //Añadir las páginas
        if (isset($document->pages)) {
            $citation .= $document->pages . '.';
        }

        $citation .= '</p>';

        return $citation;
    }

    public function generate_mla($document)
    {
        $authors = array();

        //Obtener el nombre de los autores
        foreach ($document->authors as $author) {
            $name = $author->first_name . " " . $author->last_name;
            array_push($authors, $name);
        }

        $citation = '<p>';

        //Añadir los nombres de los autores
        if (count($authors) == 1) {
            $citation .= $authors[0] . ". ";
        } elseif (count($authors) == 2) {
            $citation .= $authors[0] . " and " . $authors[1] . ". ";
        } else {
            for ($i = 0; $i < count($authors) - 1; $i++) {
                $citation .= $authors[$i] . ", ";
            }
            $citation .= "and " . $authors[count($authors) - 1] . ". ";
        }

        //Añadir el título del artículo
        $citation .= '"' . $document->title . '." ';

        //Añadir el nombre de la revista
        if (isset($document->source)) {
            $citation .= '<em>' . $document->source . '</em>, ';
        }

        //Añadir el volumen
        if (isset($document->volume)) {
            $citation .= 'vol. ' . $document->volume . ', ';
        }

        //Añadir el número
        if (isset($document->issue)) {
            $citation .= 'no. ' . $document->issue . ', ';
        }

        //Añadir el año de publicación
        $citation .= $document->year . ', ';

        //Añadir las páginas
        if (isset($document->pages)) {
            $citation .= 'pp. ' . $document->pages . '.';
        }

        $citation .= '</p>';

        return $citation;
    }

    public function generate_harvard($document)
    {
        $authors = array();

        //Obtener el nombre de los autores
        foreach ($document->authors as $author) {
            $name = $author->first_name . ' ' . $author->last_name;
            array_push($authors, $name);
        }

        $citation = '<p>';

        //Añadir los nombres de los autores
        if (count($authors) == 1) {
            $citation .= $authors[0] . '. ';
        } elseif (count($authors) == 2) {
            $citation .= $authors[0] . ' &amp; ' . $authors[1] . '. ';
        } else {
            for ($i = 0; $i < count($authors) - 1; $i++) {
                $citation .= $authors[$i] . ', ';
            }
            $citation .= 'and ' . $authors[count($authors) - 1] . '. ';
        }

        //Añadir el año de publicación
        $citation .= '(' . $document->year . ') ';

        //Añadir el título del artículo
        $citation .= '"' . $document->title . '." ';

        //Añadir el nombre de la revista
        if (isset($document->source)) {
            $citation .= '<em>' . $document->source . '</em> ';
        }

        //Añadir el volumen
        if (isset($document->volume)) {
            $citation .= $document->volume . ', ';
        }

        //Añadir el número
        if (isset($document->issue)) {
            $citation .= '(' . $document->issue . '), ';
        }

        //Añadir las páginas
        if (isset($document->pages)) {
            $citation .= 'pp. ' . $document->pages . '.';
        }

        $citation .= '</p>';

        return $citation;
    }

    public function generate_iso690($document)
    {
        $authors = array();

        //Obtener el nombre de los autores
        foreach ($document->authors as $author) {
            $name = $author->last_name . ', ' . $author->first_name;
            array_push($authors, $name);
        }

        $citation = '<p>';

        //Añadir los nombres de los autores
        if (count($authors) == 1) {
            $citation .= $authors[0] . '. ';
        } elseif (count($authors) == 2) {
            $citation .= $authors[0] . ' a ' . $authors[1] . '. ';
        } else {
            for ($i = 0; $i < count($authors) - 1; $i++) {
                $citation .= $authors[$i] . ', ';
            }
            $citation .= 'a ' . $authors[count($authors) - 1] . '. ';
        }

        //Añadir el título del artículo
        $citation .= $document->title . '. ';

        //Añadir el nombre de la revista
        if (isset($document->source)) {
            $citation .= '<em>' . $document->source . '</em>, ';
        }

        //Añadir el volumen
        if (isset($document->volume)) {
            $citation .= $document->volume . ', ';
        }

        //Añadir el número
        if (isset($document->issue)) {
            $citation .= '(' . $document->issue . '), ';
        }

        //Añadir el año de publicación
        $citation .= $document->year . ', ';

        //Añadir las páginas
        if (isset($document->pages)) {
            $citation .= 's. ' . $document->pages . '. ';
        }

        //Añadir el DOI
        //dd($document->identifiers->doi);
        if (isset($document->identifiers->doi)) {
            $citation .= 'DOI: <a href="https://doi.org/' . $document->identifiers->doi . '">' . $document->identifiers->doi . '</a>.';
        }

        $citation .= '</p>';

        return $citation;
    }

    public function generate_ieee($document)
    {
        $authors = array();

        //Obtener el nombre de los autores
        foreach ($document->authors as $author) {
            $name = $author->last_name . ', ' . $author->first_name;
            array_push($authors, $name);
        }

        $citation = '<p>';

        //Añadir los nombres de los autores
        if (count($authors) == 1) {
            $citation .= $authors[0] . ', ';
        } elseif (count($authors) == 2) {
            $citation .= $authors[0] . ' y ' . $authors[1] . ', ';
        } else {
            for ($i = 0; $i < count($authors) - 1; $i++) {
                $citation .= $authors[$i] . ', ';
            }
            $citation .= 'y ' . $authors[count($authors) - 1] . ', ';
        }

        //Añadir el título del artículo
        $citation .= '"' . $document->title . '," ';

        //Añadir el nombre de la revista
        if (isset($document->source)) {
            $citation .= '<em>' . $document->source . '</em>, ';
        }

        //Añadir el volumen
        if (isset($document->volume)) {
            $citation .= 'vol. ' . $document->volume . ', ';
        }

        //Añadir el número
        if (isset($document->issue)) {
            $citation .= 'no. ' . $document->issue . ', ';
        }

        //Añadir las páginas
        if (isset($document->pages)) {
            $citation .= 'pp. ' . $document->pages . ', ';
        }

        //Añadir el año de publicación
        $citation .= $document->year . '. ';

        //Añadir el DOI
        if (isset($document->doi)) {
            $citation .= '[Online]. Available: https://doi.org/' . $document->doi . '.';
        }

        $citation .= '</p>';

        return $citation;
    }

    public function generate_vancouver($document)
    {
        $authors = array();

        //Obtener el nombre de los autores
        foreach ($document->authors as $author) { 
            $first_lastname = explode(" ", $author->last_name); //en vancouver solo el primer apellido
            array_push($authors, $first_lastname[0] . " " . substr($author->first_name, 0, 1)."."); // inicial de nombre
        }

        $citation = '<p>';

        //Añadir los apellidos y las iniciales de los nombres de los autores
        foreach ($authors as $key => $author) {
            $name_parts = explode(" ", $author);
            $initials = "";

            foreach ($name_parts as $part) {
                $initials .= substr($part, 0, 1) . ".";
            }

            if ($key === count($authors) - 1){
                $citation .= $author ." ";
            }else{
                $citation .= $author .", ";
            }
            
        }

        //Añadir el título del artículo
        $citation .= $document->title . ". ";

        //Añadir el nombre de la revista
        if (isset($document->source) && $document->title != $document->source) {
            $citation .= "<em>" . $document->source . "</em>. ";
        }

        //Añadir el año de publicación
        $citation .= $document->year . ";";

        //Añadir el volumen
        if (isset($document->volume)) {
            $citation .= $document->volume . ":";
        }

        //Añadir las páginas
        if (isset($document->pages)) {
            $citation .= $document->pages . ".";
        }

        $citation .= '</p>';

        return $citation;
    }
}
