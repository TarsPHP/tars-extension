[![Build Status](https://travis-ci.org/TarsPHP/tars-extension.svg?branch=master)](https://travis-ci.org/TarsPHP/tars-extension)

# phptars extention Intrduction

##PHP extension capability description

In order to realize the whole system of tars package and unpacking and tup encoding and decoding in the extension, PHP extension mainly does three things:

*All data structures of tar are mapped with extension types

*Three complex types of tars are mapped with special extension types

*It provides the ability of package, unpack, code and decode of tup and tar.



##Mapping of basic types

Here is our mapping of basic types:
```
    bool => \TARS::BOOL
    char => \TARS::CHAR
    uint8 => \TARS::UINT8
    short => \TARS::SHORT
    uint16 => \TARS::UINT16
    float => \TARS::FLOAT
    double => \TARS::DOUBLE
    int32 => \TARS::INT32
    uint32 => \TARS::UINT32
    int64 => \TARS::INT64
    string => \TARS::STRING
    vector => \TARS::VECTOR
    map => \TARS::MAP
    struct => \TARS::STRUCT
```
When we need to identify specific variable types, we need to use these basic types, which are constants, from 1-14.



##Mapping of complex types

There are some special packaging and unpacking mechanisms for vector, map and struct. Therefore, special data types need to be introduced:

Vector:

` ` ` `

vector => \TARS_VECTOR

It has two member functions, push back() and push back()

The input parameter depends on what type of array the vector itself contains



For example:

$shorts = ["test1","test2"];

$vector = new \ tars \ vector (\ tars:: String); / / define a vector of type string

foreach ($shorts as $short) {

$vector - > pushback ($short); / / press test1 and test2 into vector
    }
```
map:
```
    map => \TARS_MAP
It has two member functions, push back() and push back()

The input parameter depends on what type the map itself contains



For example:
    $strings = [["test1"=>1],["test2"=>2]];
    $map = new \TARS_MAP(\TARS::STRING,\TARS::INT64); //define one key为string,value is int64的map
    foreach ($strings as $string) {
        $map->pushBack($string); //Press two elements into the map in turn, and notice that pushback receives an array, and that array has only one element
    }
```

struct:
```
    struct => \TARS_Struct
  The constructor of struct is special. It takes two parameters, classname and classfields

The first describes the name, and the second describes the information of the variables in struct



For example:
	class SimpleStruct extends \TARS_Struct {
		const ID = 0; //TARS文件中每个struct的tag
		const COUNT = 1;

		public $id; //strcut The value of each element is saved here
		public $count; 

		protected static $fields = array(
			self::ID => array(
				'name'=>'id',//struct The value of each element is saved here
				'required'=>true,//struct Whether each element is required, corresponding to the require and optional in the tars file
				'type'=>\TARS::INT64,//struct every element type
				),
			self::COUNT => array(
				'name'=>'count',
				'required'=>true,
				'type'=>\TARS::UINT8,
				),
		);

		public function __construct() {
			parent::__construct('App_Server_Servant.SimpleStruct', self::$fields);
		}
	}
   
```

##Package and unpack and code decoding

As the core function of the extension, it provides the ability of coding, decoding, packaging and unpacking of tars



###Pack and unpack

` ` ` `

//Output binary buf for basic types of packaging and unpacking methods

//There are only 1 and 3 versions of iversion. In version 1, $nameortagnum needs to be passed in tagnum, the first parameter in the method is 1, the second parameter is 2, and so on

//In version 3, $nameortagnum needs to pass in name and parameter name

$buf = \TASAPI::put*($nameOrTagNum, $value, $iVersion = 3)

$value = \TUPAPI::get*($nameOrTagNum, $buf, $isRequire = false, $iVersion = 3)



//For struct, when the object is transferred and the result is returned, it is returned in the form of an array. Its elements correspond to the member variables of the class one by one

$buf = \TUPAPI::putStruct($nameOrTagNum, $clazz, $iVersion = 3)

$result = \TUPAPI::getStruct($nameOrTagNum, $clazz, $buf, $isRequire = false, $iVersion = 3)



//For vector, pass in the vector that completes pushback

$buf = \TUPAPI::putVector($nameOrTagNum, TARS_Vector $clazz, $iVersion = 3)

$value = \TUPAPI::getVector($nameOrTagNum, TARS_Vector $clazz, $buf, $isRequire = false, $iVersion = 3)



//For map, pass in the map to complete pushback

$buf = \TUPAPI::putMap($nameOrTagNum, TARS_Map $clazz, $iVersion = 3)

$value = \TUPAPI::getMap($nameOrTagNum, TARS_Map $clazz, $buf, $isRequire = false, $iVersion = 3)



//You need to put the above packed data together for coding

$inbuf_arr[$nameOrTagNum] = $buf

` ` ` `

###Encoding and decoding

` ` ` `

//For the case of tup protocol (iversion = 3):

//In this case, the client uses encode code to send the contract, the server uses decode to decode the received package, the server uses encode to encode the returned package, and the client uses decode to decode the received package

//Code the tup protocol, and the returned results can be used for transmission and persistence
$reqBuffer = \TUPAPI::encode(
                         $iVersion = 3,
                         $iRequestId,
                         $servantName,
                         $funcName,
                         $cPacketType=0,
                         $iMessageType=0,
                         $iTimeout,
                         $context=[],
                         $statuses=[],
                         $bufs)
// Decoding the tup protocol
$ret = \TUPAPI::decode($respBuffer, $iVersion = 3)
$code = $ret['iRet']
$buf = $ret['sBuffer']

//For the tar protocol (iversion = 1):

//In this case, the client uses encode to code the contract, the server uses decodereqpacket to decode the received package, the server uses encoderssppacket to encode the returned package, and the client uses decode to decode the received package



//Client contract
$reqBuffer = \TUPAPI::encode(
                         $iVersion = 1,
                         $iRequestId,
                         $servantName,
                         $funcName,
                         $cPacketType=0,
                         $iMessageType=0,
                         $iTimeout,
                         $context=[],
                         $statuses=[],
                         $bufs)
//Server receiving and unpacking
$ret = \TUPAPI::decodeReqPacket($respBuffer)
$code = $ret['iRet']
$buf = $ret['sBuffer']

//Service end return package encodeRspPacket
$reqBuffer = \TUPAPI::encodeRspPacket(
                         $iVersion = 1,
                         $cPacketType,
                         $iMessageType,
                         $iRequestid,
                         $iRet=0,
                         $sResultDesc='',
                         $bufs,
                         $statuses=[])
                         
//Client receiving and unpacking
$ret = \TUPAPI::decode($respBuffer, $iVersion = 1)
$code = $ret['iRet']
$buf = $ret['sBuffer']
```

For more extensive use of different types of structures, please refer to tests/



##Instructions for using tars2php

Please refer to the documentation under the tars2php module:

[detailed description] (https://github.com/tarphp/tars2php/blob/master/readme.md)



##Test case



###Test case of phpunite version

For the common use of extensions, test cases are added, which are located in the / ext / testcases folder,

When testing, you only need to execute `php phpunit-4.8.36.phar test.php` to complete the execution of all test cases. It covers:

*All basic types of packaging, unpacking and coding tests

*Test of simple struct type package, unpacking and coding

*Test of simple vector type package, unpack and code

*The test of package, unpack and code of simple map type

*Test of packaging, unpacking and encoding of complex vector types (including non basic data types)

*Test of packaging, unpacking and encoding of complex map types (including non basic data types)

*Package, unpack and code test of complex struct types (nested vector and map)



In addition, testtarsclient.php and testtarsserver.php are the test cases of client contracting, server unpacking, server callback and client unpacking under the tars protocol (iversion = 1).



Note that you need to download phpunit's executable or use the pre installed phpunit tool directly for unit testing.



###At the same time, it points out the test cases of PHPT version

After the extension is installed, execute make test.
