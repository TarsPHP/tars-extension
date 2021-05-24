# phptars (version 2.3.0) 扩展使用说明

**特别说明：本扩展仅支持 php >= 8.0 以上的版本**

未对 PHP 7.x 版本做过测试，不确定是否支持.

由于官方项目维护不及时，暂不支持 PHP8，所以我创建了这个版本.

官方项目地址: https://github.com/TarsPHP/tars-extension

## 零、安装指令参考

```shell
cd /root/ && \
wget https://github.com/lanlin/phptars-php8/archive/refs/tags/v2.3.0.tar.gz && \
tar -zxvf v2.3.0.tar.gz && \
cd ./phptars-php8/ && \
phpize --clean && \
phpize && \
./configure --enable-phptars && \
make clean && \
make && \
make install && \
mkdir -p /root/phptars && \
cp ./tars2php.php /root/phptars && \
cd /root/ && \
rm -rf ./phptars-php8
```

最后将 `extension=phptars.so` 添加到你的 `php.ini` 中即可。

## 一、php 扩展能力说明

为了在扩展中实现 tars 打包解包和 tup 编码解码的全部体系, 所以 php 扩展主要做了三件事情:

* 将 tars 的所有数据结构进行了扩展类型的映射
* 将 tars 的三种复杂类型进行了特殊的扩展类型的映射
* 提供了 tup 和 tars 协议的打包解包与编码解码的能力。

## 二、基本类型的映射

如下是我们对基本类型的映射:
```php
    bool   => \TARS::BOOL,
    char   => \TARS::CHAR,
    uint8  => \TARS::UINT8,
    short  => \TARS::SHORT,
    uint16 => \TARS::UINT16,
    float  => \TARS::FLOAT,
    double => \TARS::DOUBLE,
    int32  => \TARS::INT32,
    uint32 => \TARS::UINT32,
    int64  => \TARS::INT64,
    string => \TARS::STRING,
    vector => \TARS::VECTOR,
    map    => \TARS::MAP,
    struct => \TARS::STRUCT,
```

当我们需要标识具体的变量类型的时候, 就需要用到这些基本的类型了, 这些类型都是常量, 从 1-14。

## 三、复杂类型的映射

针对 vector、map、struct 三种基本的类型, 有一些特殊的打包解包的机制, 因此需要引入特别的数据类型:

### 1. vector => \TARS_VECTOR:

它同时具有两个成员函数 pushBack() 和 push_back().
入参为取决于 vector 本身是包含什么类型的数组

```php
$shorts = ['test1', 'test2'];

// 定义一个 string 类型的 vector
$vector = new \TARS_VECTOR(\TARS::STRING);

foreach ($shorts as $short)
{
    // 依次吧 test1，test2 两个元素，压 入vector 中
    $vector->pushBack($short);
}
```

### 2. map => \TARS_MAP:

它同时具有两个成员函数 pushBack() 和 push_back().
入参为取决于 map 本身包含什么类型

```php
$strings = [ ['test1' => 1], ['test2' => 2] ];

// 定义一个 key 为 string, value 是 int64 的 map
$map = new \TARS_MAP(\TARS::STRING,\TARS::INT64);

foreach ($strings as $string)
{
    // 依次把两个元素压入 map 中，注意 pushBack 接收一个 array，且 array 只有一个元素
    $map->pushBack($string);
}
```

### 3. struct => \TARS_Struct:

struct 的构造函数比较特殊, 接收 classname 和 classfields 两个参数.
第一个描述名称,第二个描述struct内的变量的信息

```php
class SimpleStruct extends \TARS_Struct
{
    const ID    = 0;   // TARS 文件中每个 struct 的 tag
    const COUNT = 1;

    public $id;        // strcut 中每个元素的值保存在这里
    public $count; 

    protected static $fields =
    [
        self::ID =>
        [
            'name'     => 'id',         // struct 中每个元素的名称
            'type'     => \TARS::INT64, // struct 中每个元素的类型
            'required' => true,         // struct 中每个元素是否必须，对应 tars 文件中的 require 和 optional
        ],
        self::COUNT =>
        [
            'name'     => 'count',
            'type'     => \TARS::UINT8,
            'required' => true,
        ],
    ];

    public function __construct()
    {
        parent::__construct('App_Server_Servant.SimpleStruct', self::$fields);
    }
}
```

## 四、打包解包与编码解码

作为扩展的核心功能, 就是提供 tars 的编解码和打包解包的能力:

### 1. 打包解包

```php
// 针对基本类型的打包和解包的方法, 输出二进制 buf
// iVersion 只有 1 和 3 两个版本，1 版本时 $nameOrTagNum 需要传入 tagNum, 方法里面第一个参数为 1 第二个参数为 2 以此类推
// 3 版本时 $nameOrTagNum 需要传入 name, 参数名
$buf   = \TASAPI::put*($nameOrTagNum, $value, $iVersion = 3);
$value = \TUPAPI::get*($nameOrTagNum, $buf, $isRequire = false, $iVersion = 3);

// 针对 struct, 传输对象, 返回结果的时候, 以数组的方式返回, 其元素与类的成员变量一一对应
$buf    = \TUPAPI::putStruct($nameOrTagNum, $clazz, $iVersion = 3);
$result = \TUPAPI::getStruct($nameOrTagNum, $clazz, $buf, $isRequire = false, $iVersion = 3);

// 针对 vector, 传入完成 pushBack 的 vector
$buf   = \TUPAPI::putVector($nameOrTagNum, TARS_Vector $clazz, $iVersion = 3);
$value = \TUPAPI::getVector($nameOrTagNum, TARS_Vector $clazz, $buf, $isRequire = false, $iVersion = 3);

// 针对 map, 传入完成 pushBack 的 map
$buf   = \TUPAPI::putMap($nameOrTagNum, TARS_Map $clazz, $iVersion = 3);
$value = \TUPAPI::getMap($nameOrTagNum, TARS_Map $clazz, $buf, $isRequire = false, $iVersion = 3);

// 需要将上述打好包的数据放在一起用来编码
$inbuf_arr[$nameOrTagNum] = $buf;
```

### 2. 编码解码

```php
// 针对 tup 协议 (iVersion=3) 的情况：
// 这种情况下客户端发包用 encode 编码，服务端收包用 decode 解码，服务端回包用 encode 编码，客户端收包用 decode 解码

// 进行tup协议的编码,返回结果可以用来传输、持久化
$reqBuffer = \TUPAPI::encode(
    $iVersion = 3,
    $iRequestId,
    $servantName,
    $funcName,
    $cPacketType = 0,
    $iMessageType = 0,
    $iTimeout,
    $context = [],
    $statuses = [],
    $bufs
);
                         
// 进行 tup 协议的解码
$ret  = \TUPAPI::decode($respBuffer, $iVersion = 3);
$buf  = $ret['sBuffer'];
$code = $ret['iRet'];

// 针对 tars 协议 (iVersion=1) 的情况：
// 这种情况下客户端发包用 encode 编码，服务端收包用 decodeReqPacket 解码，服务端回包用 encodeRspPacket 编码，客户端收包用 decode 解码

// 客户端发包
$reqBuffer = \TUPAPI::encode(
    $iVersion = 1,
    $iRequestId,
    $servantName,
    $funcName,
    $cPacketType = 0,
    $iMessageType = 0,
    $iTimeout,
    $context = [],
    $statuses = [],
    $bufs
);
                         
// 服务端收包，解包
$ret  = \TUPAPI::decodeReqPacket($respBuffer);
$buf  = $ret['sBuffer'];
$code = $ret['iRet'];

// 服务端回包，打包 encodeRspPacket
$reqBuffer = \TUPAPI::encodeRspPacket(
    $iVersion = 1,
    $cPacketType,
    $iMessageType,
    $iRequestid,
    $iRet = 0,
    $sResultDesc = '',
    $bufs,
    $statuses = []
);
                         
// 客户端收包，解包
$ret  = \TUPAPI::decode($respBuffer, $iVersion = 1);
$buf  = $ret['sBuffer'];
$code = $ret['iRet'];
```

对于不同类型的结构的打包解包的更丰富的使用请参考 tests/

## 五、tars2php（自动生成php类工具）使用说明

请参见 tars2php 模块下的文档说明:
[详细说明](https://github.com/TarsPHP/tars2php/blob/master/README.md)

## 六、测试用例

### 1. phpunit 版本的测试用例

针对扩展的常见使用, 增加了测试用例, 位于 /ext/testcases 文件夹下,
测试时只需要执行 `phpunit TestTars.php` 即可完成所有测试用例的执行。其中覆盖到了:

* 所有基本类型的打包解包和编码的测试
* 简单 struct 类型打包解包和编码的测试
* 简单 vector 类型的打包解包和编码的测试
* 简单 map 类型的打包解包和编码的测试
* 复杂 vector 类型(包含非基本数据类型)的打包解包和编码的测试
* 复杂 map 类型(包含非基本数据类型)的打包解包和编码的测试
* 复杂 struct 类型(嵌套 vector 和 map)的打包解包和编码的测试

另外 TestTarsClient.php 和 TestTarsServer.php 是 tars 协议（iVersion=1）情况下客户端发包，服务端解包 和 服务端回包，客户端解包的测试用例。

注意，需要自行下载 phpunit 的可执行文件，或直接使用预先安装好的 phpunit 工具，进行单元测试。

### 2. 同时指出 phpt 版本的测试用例

安装完成扩展后，执行 `make test` 即可。