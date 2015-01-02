<?php
	ini_set('max_execution_time', 60*60*5);

    header('Content-Type: text/html; charset=UTF-8');

    function microtime_float()
    {
        list($useg, $seg) = explode(" ", microtime());
        return ((float)$useg + (float)$seg);
    }
	class ApiController extends BaseController
	{
		public function index()
	    {
    		//$names = Name::all();
    		//$names = Name::where('popularity', '>', 4.5)->where('country', 'co')->where('composition', 1)->get();
    		//$names = Name::where('value', 'andrea')->get();
    		$names = Name::where('popularity', 5)->project(array('value' => 1, 'gender' => 1, '_id' => 0))->take(150)->orderBy('value', 'asc')->get();
    		$names = array('names' => $names);
    		$names = json_encode($names,JSON_PRETTY_PRINT);

	        return $names;
	    }

	    public function getByName($name)
	    {
            
	    	$names = Name::where('value', 'regexp', "/.*$name.*/i")->project(array('value' => 1, 'gender' => 1, 'country' => 1, 'popularity' => 1,'_id' => 0))->get();

	    	$names = array('names' => $names);
    		$names = json_encode($names,JSON_PRETTY_PRINT);

	        return $names;
	    }

	    public function customQuery()
	    {
	    	//$maxPopularity = Name::where('country', 'co')->where('gender', 'f')->max('popularity');

	    	//$names = Name::where('popularity', $maxPopularity)->where('country', 'co')->where('gender', 'f')->project(array('value' => 1, 'gender' => 1, 'country' => 1, 'popularity' => 1,'_id' => 0))->take(15)->get();


	    	//$names = Name::all()->groupBy('country')->count();
	    	/*$names = DB::collection('names')
                 ->where('country', Name::raw('count(*) as total'))
                 ->groupBy('country')
                 ->get();*/

            $countries = Name::distinct('country')->get();

            $max = 0;
            $codeMax = '';
            foreach ($countries as $country)
            {
            	$c = json_decode($country,true);

            	$code = $c[0];

            	$count = Name::where('country', $code)->count();
            	
            	if($count > $max)
            	{
            		$max = $count;
            		$codeMax = $code;
            	}
            }

            $country = Country::where('iso2', $codeMax)->project(array('_id' => 0, 'name' => 1, 'iso2' => 1, 'iso3' => 1, 'code' => 1))->first();

            $names = array('country' => $country, 'counter' => $max);

	    	//var_dump($names);

	    	//$names = array('names' => $names);
    		$names = json_encode($names,JSON_PRETTY_PRINT);

	        return $names;
	    }

        public function test()
        {
            //Se obtiene el nombre y el iso2 de todos los paises en la base de datos
            $countries = DB::collection('countries')->project(array('_id' => 0, 'name' => 1, 'iso2' => 1))->get();

            $orderedCountries = array();

            //Se ordenan por medio de un array asociativo según el iso2 para su fácil ubicación
            foreach ($countries as $country)
            {
                $iso2 = $country['iso2'];
                $name = $country['name'];
                $orderedCountries[$iso2] = $name;
            }


            //-------------------------

            //Se obtiene el nombre y el codigo de todos los lenguajes en la base de datos
            $languages = DB::collection('languages')->project(array('_id' => 0, 'name' => 1, 'code' => 1))->get();

            $orderedLanguages = array();

            //Se ordenan por medio de un array asociativo según el iso2 para su fácil ubicación
            foreach ($languages as $language)
            {
                $code = $language['code'];
                $name = $language['name'];
                $orderedLanguages[$code] = $name;
            }

            $collection = ''; //Se inicializa una colección que tendrá los nombres válidos
            $correctos = 0; //Se inicia en cero el contador de nombres correctos

            $collection2 = ''; //Esta tendrá los nombres inválidos
            $incorrectos = 0; //Se inicia en cero el contador de nombres incorrectos

            $cants = array(); //Se inicializa un array que contendrá las cantidades por cada consulta

            //Se establece el token
            $token = 'CAAFFZB69W4icBAKZCczTlqQha5SMM1T0o82jcknxl7mPYyF014BZBD7JKi0A617ZAClHnFtQ42mR95eIUaiBnLm1Esno0f3a3sPiXZCbzyc0SOS2pr0IkkWK4dj79Eydug23ZAH5HPEYwR0VXBohkNex7jVq1VLxYY8MyLIgWp8SxrbArtqaM7ZC8ZACpZChYFtuGMM7sGmrzICjc1DJQdCow';

            $tiempo_inicio = microtime_float(); //Se obtiene el tiempo inicial, justo antes de hacer la petición
            for ($i = 'a'; $i <= 'c'; $i++) //Se hace un ciclo recorriendo letras
            {
                try//Se intenta hacer la petición, pues en algún momento puede fallar
                {
                    //Se hace la petición con la letra específica
                    $names = file_get_contents("https://graph.facebook.com/search?q=$i&type=user&fields=name,locale,first_name,last_name,gender&access_token=$token");

                    //Se parsea la respuesta obtenida
                    $jsonNames = json_decode($names, true);

                    //Se accede a la posición data de la respuesta (es la que contiene cada nombre)
                    $jsonNames = $jsonNames['data'];

                    $cants[$i] = sizeof($jsonNames);

                    //Se recorre cada nombre, para comenzar a formar el documento correspondiente
                    foreach ($jsonNames as $name)
                    {
                        //Se obtiene el id
                        $id = $name['id'];

                        //Se obtiene el nombre completo
                        $completeName = $name['name'];

                        //Se obtiene el apellido
                        $lastname = $name['last_name'];

                        //El nombre real es el nombre declarado menos el apellido (hay casos en que first_name no es completo ver perfil JuanDMeGon por ejemplo)
                        $realName = str_replace(" $lastname", '', $completeName);

                        $locale = $name['locale'];

                        $partition = explode('_', $locale);

                        $languageCode = strtolower($partition[0]); //La primera posición es el codigo del lenguaje
                        $countryCode = strtolower($partition[1]); //La segunda y última particion es el código iso2 del país

                        if(isset($orderedLanguages[$languageCode]))
                        {
                            $language = $orderedLanguages[$languageCode];//Se obtiene el nombre del lenguaje
                        }
                        else
                        {
                            $language =  null;
                        }

                        if($countryCode === 'la')//Si FB devuelve codigo de país LA es latino américa
                        {
                            $country = 'mexico, colombia, argentina, ecuador, venezuela, uruguay, chile, bolivia';   
                        }
                        else
                        {
                            if(isset($orderedCountries[$countryCode]))
                            {
                                $country = $orderedCountries[$countryCode];//Se obtiene el nombre del país
                            }
                            else
                            {
                                $country = null;
                            }
                        }

                        //Se define una variable temporal
                        //Notar que se vuelve a codificar el nombre OJO quedando en el formato (\u041d\u0486f, etc)
                        //Y reemplazando los \ por | puesto que los \ generaban problemas con la expresión regular
                        $tmp = str_replace('\\', '|', json_encode($realName));

                        //Se declara el patrón básico para validar nombres
                        //"([a-zA-Z0-9\\]{0,})([ ]{0,1}([a-zA-Z0-9\\]{2,}))"
                        //$right = preg_match('/^([A-Za-z]{2,})( [A-Za-z]{2,}){0,1}$/', $realName);
                        $right = preg_match('/^"([a-zA-Z0-9|]{0,})([ ]{0,1}([a-zA-Z0-9|]{2,}))"$/', $tmp);


                        if($right === 1)//Si la verificación tuvo coincidencias
                        {
                            //Se agrega a la colección de válidos
                            $collection .= "<tr><td></td><td>$realName</td><td>$country</td><td>$language</td><td>$id</td></tr>";
                            $correctos++;
                        }
                        elseif($right === 0) //Si no hubo coincidencias
                        {   
                            $encoded = json_encode($realName);
                            $collection2 .= "<tr><td>$encoded</td><td>$realName</td><td>$country</td><td>$language</td><td>$id</td></tr>";
                            $incorrectos++;
                        }
                        else//Si finalmente falló la verificación (retornó false)
                        {
                            echo "Falló la verificación: tmp = $tmp";
                        }

                        
                    }
                }
                catch(Exception $e)
                {
                    echo "Fallo petición en: -- $i --. $e";
                    break;
                }
            }

            $relacion = ($incorrectos/$correctos)*100;
            $tiempo_fin = microtime_float(); //Se obtiene el tiempo final, justo despues del fin del ciclo
            $tiempo_total = $tiempo_fin - $tiempo_inicio;
            $total = $correctos + $incorrectos;//Se calcula el total de nombres obtenidos

            echo "<p><strong>Correctos</strong>: $correctos. <strong>Incorrectos:</strong> $incorrectos ($relacion%). <strong>Total:</strong> $total. <strong>Tiempo total: </strong>$tiempo_total</p>";

            echo '<h1>Correctos</h1>';
            echo '<table><tr><th>Encoded</th><th>Name</th><th>Country</th><th>Language</th><th>Id</th></tr>';
            echo $collection;
            echo '</table>';

            echo '<h1>Incorrectos</h1>';
            echo '<table><tr><th>Encoded</th><th>Name</th><th>Country</th><th>Language</th><th>Id</th></tr>';
            echo $collection2;
            echo '</table>';

            echo '<h1>Cantidades</h1>';
            echo '<table><tr><th>Query</th><th>Cantidad</th></tr>';

            $total = 0;//Se inicializa el total de cantidades
            foreach ($cants as $query => $cantidad)//Se recorren las cantidades para presentarlas en una tabla
            {
                $total += $cantidad;
                echo "<tr><td>$query</td><td>$cantidad</td></tr>";
            }

            echo "<tr><td><strong>Total</strong></td><td><strong>$total</strong></td></tr>";

            echo '</table>';

            exit;
        }

        public function test2()
        {
            $token = 'CAAFFZB69W4icBAPwMsKAbPvZCU9ZADhfkAmaYrDRFOZAl0EWxPZBqHPCS78D5ieYsoxSH5aJdXOkeo4F9ceheeUWfIZBCKkYRTZAm50oZBk6xDncxrgDGGwZAPm9Tb2qqubh4CnxbWdcBfhnLU7nld5u8bjLtFLElHKMzCVAPKDv9grvcVwhxxnwZBnncOmU2mIZA0INVBTV1hKZACQnIDM0WKEO';

            $url = "https://graph.facebook.com/v1.0/search?q=a&type=user&fields=name,locale,first_name,last_name,gender&access_token=$token";


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);

            // Set so curl_exec returns the result instead of outputting it.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
            // Get the response and close the channel.
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }

        public function tojson()
        {
            //Is declared a collection which going to contain all the names (proper and improper) separated in one array
            $collection = array('names' => array('proper' => array(), 'improper' => array()));

            $proper = 0; //Begin a proper names counter in zero
            $improper = 0; //Begin an improper names counter in zero

            //Is stablished the token
            $token = 'CAAFFZB69W4icBAH1kJG3H9ZCXTmrJK8E5NEmmuLD4g9snLgVrdZAqWTr0luS3ot8JbPdGdZAy4svTzCxApUDRa4bihSdgE7zT2xUZB9D4KgfPR6v5rZC1ZC61av2acZA6yBVo0cQ7xAb3PDoLZA0KIMNpM1K3PieDYpXDsGvTvoQBi6jekoMEVJgZC1X6l5T3VeGZCbH6s45Vfcb4MR6CHooEhX';
            
            $startTime = microtime_float(); //Obtaining the execution start time

            $queries = '|';//Initializing a variable with all the running queries
            for ($i = 'a'; $i <= 'y'; $i++) //Loop through letters
            {
                try//Because the request could fail, we use try
                {
                    //Request by the loop letter (q=$i)
                    $names = file_get_contents("https://graph.facebook.com/search?q=$i&type=user&fields=name,locale,first_name,last_name,gender&access_token=$token");

                    $queries .= "$i|"; //Adding the query char to the record

                    //Parsing response into an associative array(true)
                    $jsonNames = json_decode($names, true);

                    //The data possition have every name in the response
                    $jsonNames = $jsonNames['data'];

                    //Runing hover every name to beggin create the json document
                    foreach ($jsonNames as $name)
                    {
                        //Is obtained the complete name
                        $completeName = $name['name'];

                        //Is obtained the lastname
                        $lastname = $name['last_name'];

                        //A real name is obtained from the declared name less the lastname (in some cases first_name is not complete ex:JuanDMeGon)
                        $realName = str_replace(" $lastname", '', $completeName);

                        //The composition is the number of spaces plus 1
                        $composition = substr_count($realName, ' ') + 1;

                        //Is obtained the gender value

                        //In some strange cases the gender is not defined
                        //Watch https://graph.facebook.com/634631106/ for example which have not gender
                        //Occording with the documents https://developers.facebook.com/docs/graph-api/reference/v2.2/user
                        //Gender could be omitted for a custom value
                        if(isset($name['gender']))
                        {
                            $gender = $name['gender'];
                        }
                        else
                        {
                            $gender = null;
                        }

                        if($gender === 'male')//If gender is male, so code is 'm'
                        {
                            $gender = 'm';
                        }
                        elseif($gender === 'female') //If gender is female, so code is 'f'
                        {
                            $gender = 'f';
                        }
                        else//Strange case (almost impossible)
                        {
                            $gender = null;
                        }

                        $locale = $name['locale']; //Is obtained the locale value (which is lang_country codes)

                        $partition = explode('_', $locale); //Is exploded into an array the langCode and the countryCode

                        $languageCode = strtolower($partition[0]); //First position is the language code
                        $countryCode = strtolower($partition[1]); //Second positions is the iso2 country code

                        /*
                            * Notice: Take into account that some returned language and
                            * country codes does nos exist in the ISO standard (rare cases)
                            * and the LA case (for LatinAmerica)
                        */

                        //Defining a temporal variable
                        //Notice that the name is encoded again keepeng the format like (\u041d\u0486f, etc)
                        //Reeplacing the '\' by '|' because the  '\' create some problems whit regex
                        $tmp = str_replace('\\', '|', json_encode($realName));

                        //Basic pattern to validate a name
                        $right = preg_match('/^"([a-zA-Z0-9|]{0,})([ ]{0,1}([a-zA-Z0-9|]{2,}))"$/', $tmp);

                        if($right === 1)//If verification match
                        {
                            $finalName = new Name; //Is created a Name object
                            $finalName->setAttributes($realName, 3, $gender, $composition, '', '', $countryCode, $languageCode);

                            //The builded name is added to the proper names collection
                            $collection['names']['proper'][] = $finalName;

                            //The proper names counter is increased in 1
                            $proper++;
                        }
                        elseif($right === 0) //If not match
                        {
                            $finalName = new Name; //Is created a Name object
                            $finalName->setAttributes($realName, 3, $gender, $composition, '', '', $countryCode, $languageCode);

                            //The builded name is added to the improper collection
                            $collection['names']['improper'][] = $finalName;

                            //The improper names counter is increased in 1
                            $improper++;
                        }
                        else//If fail (for some reason) show the error
                        {
                            echo "Verification fail: tmp = $tmp";
                        }                        
                    }
                }
                catch(Exception $e)//If the request fail, so catch and continue the loop to next letter
                {
                    echo "Request fail at: -- $i --. $e";
                    continue;
                }
            }

            $percentage = ($improper/$proper)*100;//Calculating the improper names relation
            $end_time = microtime_float(); //Obtaining the end time at the end of the loop
            $total_time = $end_time - $startTime; //Calculating total execution time
            $total = $proper + $improper;//Calculating the total names counter

            /*
                *  
                *   Inserting statistics into the collections
                *
            */
            $collection['proper'] = $proper;
            $collection['improper'] = "$improper ($percentage%)";
            $collection['total_names'] = $total;
            $collection['execution_time'] = $total_time;
            $collection['query_string'] = $queries;

            //Parsing the collections into a final JSON

            $final = json_encode($collection, JSON_PRETTY_PRINT);

            echo $final;

            exit;
        }
	}