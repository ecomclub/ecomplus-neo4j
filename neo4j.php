<?php

require_once 'vendor/autoload.php';
use Neoxygen\NeoClient\ClientBuilder;// add libary Neo4J
$user = 'wis'; // DB User
$password = 'neo4j'; //User Password
$client = ClientBuilder::create() // create connection Neo4j
    ->addConnection('default', 'http', 'localhost', 7474, true, $user, $password ) // initial connection
    ->build();
// $version = $client->getNeo4jVersion();

function createNodeProductNeo4j($Product,$storeID){
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
  /*
    function to create the product in Neo4J, verifies if this product has any category,
    if it has it, it creates the category node if it does not exist and relates the product
    to the categories that it belongs to.
  */

  // but check if there are tags, if there are converts them to a string.
  $vBrands = "";   // start string Brands as empty
  if (is_array($Product['brands'])) { //  Check if brands is an array, if true create var Brands
    // for each brand, add the string $Brands
    for ($i=0; $i < count($Product['brands']) ; $i++) {
      $vBrands = $vBrands.$Product['brands'][$i].","; // concatenates the brands
    }
  }
  /* create product node, after creating relationship with the store and
  remove relationship with category, if there is*/

  // create only one node Products and relationship with store
  $query = 'MATCH (s:Store {id:{idStore}})'; //query seach store
  $query .= ' MERGE (p:Product {id:{idProduct}, storeID:{idStore}}) set p.name={nameProduct} set p.brands={brandsProduct}'; // query to create Product
  $query .= ' MERGE (s)-[:Has]->(p)'; // query to create relationship Product and Store
  $query .= ' WITH p MATCH (p)-[pc:BelongsTo]->()'; // query to seach product relationship with  category
  $query .= ' DELETE pc'; // delete relationship
  // parametrs for products, id, name and brands
  $parameters = array('idProduct' =>  $Product['_id'],'nameProduct' => $Product['name'],
                     'brandsProduct' => $vBrands,
                     'idStore:' => $storeID
                    ); //
  /* execute query */
  $client->sendCypherQuery($query, $parameters);
  /* check categories, create category node and relationship with product and store, if the product has category */
  if(is_array($Product['categoreis'])){ // Check if categories is an array, if true create category node
    // Categories is an array, create category node for each category exists in the array
    for ($i=0; $i < count($Product['categoreis']); $i++) {
      $q0 = "MATCH (s:Store {id:{idStore}})";
      $q1 = " MATCH (p:Product {id:{idProduct}, storeID:{idStore}})"; // query to create Product
      $q2 = " MERGE (c:Category {id:{idCategory}, storeID :{idStore}}) set c.name = {nameCategory} ";// query to create Category
      $q3 = " MERGE (p)-[:BelongsTo]->(c)"; // query to create relationship Product and Category
      $q4 = " MERGE (s)-[:Has]->(c)"; // query to create relationship Category and Store
      // concatenates all querys to execute
      $query = $q0.$q1.$q2.$q3.$q4;
      /* parametrs for query */
      // parametrs for products, id, name, brands and StoreId
      $parameters = array('idProduct' =>  $Product['_id'],'nameProduct' => $Product['name'],
                          'idStore:' => $storeID,
                          'idCategory' => $Product['categoreis'][$i]['_id'], // parametrs for category, id and name
                          'nameCategory' => $Product['categoreis'][$i]['name']
                        );
      /* execute query */
      $client->sendCypherQuery($query, $parameters);
    }
  }
}

function deleteStoreByIdNeo4j($storeID){// function to delete the store node, all relationships, and all store-related nodes
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
  $parameters = array('storeId' => $storeID ); // parametrs for seach
  //**********
  $q1 = "MATCH (p:Product {storeID:{storeId}}) MATCH (p)-[po:Buy]->() "; // query to search product relationship with order
  $d1 = 'DELETE po'; // delete relationship
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
  $q1 = "MATCH (p:Product {storeID:{storeId}}) MATCH (p)-[pc:BelongsTo]->() "; // query to seach product relationship with  category
  $d1 = 'DELETE pc'; // delete relationship
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
  $q1 = "MATCH (s:Store{id:{storeId}}) MATCH (s)-[sp:Has]->() "; // query to seach store relationship with category,product and order
  $d1 = 'DELETE sp'; // delete relationship
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
  $q1 = "MATCH (o:Order {storeID:{storeId}}) "; // query to seach order by StoreId
  $d1 = 'DELETE o'; // delete NOdes Order
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
  $q1 = "MATCH (c:Category {storeID:{storeId}}) "; // query to seach category by StoreId
  $d1 = 'DELETE c'; // delete Nodes Category
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
  $q1 = "MATCH (p:Product {storeID:{storeId}}) "; // query to seach product by StoryId
  $d1 = 'DELETE p'; // delete Nodes Product
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
  $q1 = "MATCH (s:Store{id:{storeId}}) "; // query to seach store by id
  $d1 = 'DELETE s'; // delete node Store
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
}
function deleteProductNeo4j($storeID,$productID){// function to delete the product node
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
  $parameters = array('storeId' => $storeID,'productId' =>$productID ); // parametrs for seach
  //**********
  $q1 = "MATCH (p:Product {id:{productId},storeID:{storeId}}) MATCH (p)-[po:Buy]->() "; // query to search product relationship with order
  $d1 = 'DELETE po'; // delete relationship
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
  $q1 = "MATCH (p:Products{id:{productId},storeID:{storeId}}) MATCH (p)-[pc:BelongsTo]->() "; // query to seach product relationship with  category
  $d1 = 'DELETE pc'; // delete relationship
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
  $q1 = "MATCH (p:Product {id:{productId},storeID:{storeId}}) "; // query to seach product by StoryId
  $d1 = 'DELETE p'; // delete Nodes Product
  $query = $q1.$d1; // concatenates queres
  $client->sendCypherQuery($query, $parameters); // execute query with parametrs
  //**********
}

function getStoreNeo4j(){
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
  $query = 'MATCH (s:Store) RETURN s ';
   //cypher to ..
  $result = $client->sendCypherQuery($query);// function to create Node by passing the value of the cypher and the parameters
  $publicResult = $result->getBody(); /* get public reponse, because $result is protected
  see ../vendor/neoxygen/neoClient/src/Request/Response.php */
  $response = $publicResult["results"][0]["data"];
  // filtering results
  /* exemple of filtering */
  $res = [];
  for ($i=0; $i < count($response) ; $i++) {
    $sid = $response[$i]["row"][0]['id'];
    array_push($res,array('id' => $sid) );
  }
  return $res; // return result
}

function createOrderNeo4j($order,$storeID){
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
  if (is_array($order['items'])){
    $allProducts = $order['items'];
    for ($i=0; $i <count($allProducts) ; $i++) {
      // create relationships with Products and orders
      //$productID = $allProducts[$i]['product_id']; // get product id
      //$parameters = array('idStore' => $storeID,'productId' =>$productID ); // parametrs for seach
      // $q0 = "MERGE (o:Order {id:{id},storeID:{idStore}})"; // marge or match
      // $q1 = "MATCH (p:Product {id:{productId},storeID:{idStore}})"; // seach product by id
      // $q2 = "MERGE (p)-[:Buy]->(o)"; // create relationship product 
      // $query = $q0.$q1.$q2;
      // $client->sendCypherQuery($query, $parameters); // execute query with parametrs
    }
  }
}

?>
