# Общие сведения
В нашей CRM системе [SalesRender](https://salesrender.com/ru) существует встроенный функционал для перепродажи лидов,
который может быть использован в нескольких сценариях. Это делается очень просто, но для начала важно понимать сценарии,
в которых используется продажа и перепродажа лидов.

### Для **вебмастеров/арбитражников**
Вы как вебмастер аккумулируете свои лиды в SalesRender и настраиваете их автоматическую продажу в разные CPA-сети в
заданных пропорциях. Если у CPA-сети лид считается дублем, или происходит какая-то ошибка, то в CRM будет произведена
автоматическая перепродажа лида в другую CPA-сеть. При этом у вас в SalesRender по каждой CPA-сети будет свой, независимый
баланс и система контроля корректности ставок, времени холда и статусов ваших лидов, которая поможет защитить вас от
шейва и легко проводить сверки.

### Для создания своих CPA-сетей или арбитражных команд
Вы можете использовать SalesRender для создания собственной CPA-сети. Каждый из ваших вебмастеров/арбитражников будет
иметь свой личный кабинет, свою статистику, свой баланс и свои ставки. Все лиды будут поступать в CRM SalesRender где будут
автоматически продаваться конечным рекламодателям или в другие CPA-сети в заданных пропорциях, по вашим сценариям и с
отдельными ставками. Можно осуществлять множественные продажи одного и того же лида разным рекламодателям/CPA-сетям на
разных условиях (за заявку/апрув/выкуп) при этом можно использовать "домонетку" для ваших вебов за каждый повторно
проданный лид, а можно ее отключить.

### Для продажи уже отработанных лидов у рекламодателя
Если у вас свой интернет-магазин или товарка, то вы можете продавать уже отработанные (выкупленные) лиды своим партнерам
по вашим сценариям. Выкупленный лид может быть перепродан один или более раз разным партнерам по разным ценам и на разных
условиях (за заявку/апрув/выкуп). Их можно перепродавать как сразу после выкупа, так и с некоторой задержкой. Главное -
не забудьте взять с ваших покупателей соответствующее согласие на обработку и передачу их персональных данных третьим лицам.

### Для быстрой интеграцией с множеством CPA сетей
Мы стараемся самостоятельно разрабатывать скрипты интеграции для всех популярных CPA-сетей и даже держим их на своих
серверах. Поэтому аккумулируя лиды в SalesRender вы получаете возможность продавать лиды в большинство популярных CPA-сеток
вообще не думая о технических аспектах.

### Для проверки отмены и апрува
Аккумулируя свои лиды в SalesRender, вы как вебмастер или арбитражная команда можете в один клик звонить прямо из браузера
на отмененные лиды или запускать роботизированный опрос, чтобы узнать, действительно ли лид был отменен, и по какой причине.

## Технические аспекты
Все процессы и сценарии продажи и перепродажи лидов настраиваются из интерфейса [SalesRender](https://salesrender.com/ru)
в несколько кликов. Однако, у каждого рекламодателя/CPA-сети есть свои особенности API, поэтому для интеграции нужно по
примерам написать всего 2 скрипта.

В общем виде последовательность такая:

1) **В интерфейсе SalesRender**, вы заполняете поля:
    - Путь к папке со скриптами, например `https://example.com/resale/`
    - API-токен для доступа к системе рекламодателя или CPA-сети, например `289ac134c13a449aac56039e6a7d5688`
    - Прочие параметры, нужные для скрипта ([о них ниже](#schemajson))
    - Отмечаете галочками поля заказа, которые вы хотите передавать
2) SalesRender для продажи лида отправит POST-запрос на `https://example.com/resale/push.php`, который:
    - Получит в запросе данные лида, параметры и API-токен в системе рекламодателя или CPA-сети
    - Построит и отправит запрос в систему рекламодателя или CPA-сеть с данными лида
    - Получит в ответ данные о проданном лиде (его id в системе рекламодателя или CPA-сети, статус итд)
    - Отдаст ответ в формате json с данными о http-кодом 201
3) Настройка webhook/postback в системе рекламодателя или CPA-сети:
    - Вы получаете url для вебхука в интерфейсе SalesRender вида `https://example.com/resale/pull.php?cl=de&cid=10&token=289ac134c13a449aac56039e6a7d5688`,
      к которому можно добавлять любые параметры, но нельзя удалять существующие. Обратите внимание, что SalesRender для
      приема вебхука использует тот же токен, что и API-токен для доступа к системе рекламодателя или CPA-сети.
    - Вы указываете полученный url в кабинете рекламодателя или CPA-сети
    - Файл `pull.php` получает webhook/postback с данными об изменении лида, синхронно преобразуя и отправляя их в SalesRender,
      с указанием токена и параметров `cl` и `cid`

### push.php
Этот файл отвечает за получение стандартизированных данных из SalesRender, их конвертацию и отправку рекламодателю/CPA-сети
для продажи/перепродажи лида. **Скрипт всегда получает единообразную структуру данных**, поэтому вам не нужно делать в
коде проверки на существование ключа через `isset()`. Структура всегда одинаковая, в каждом ключе либо значение, либо `null`.
**При этом, какие данные отдавать, а какие скрывать (отдавать в виде `null`) вы настраиваете галочками внутри SalesRender.**

Ниже приведен пример **максимально полных данных**, которые получит `push.php` в `$_POST`
```php
[
    //Токен API для системы рекламодателя или CPA-сети, задается в интерфейсе SalesRender
    'token' => '289ac134c13a449aac56039e6a7d5688',
    //Произвольные параметры, которые задаются в интерфейсе SalesRender, подробнее о них ниже в разделе schema.json
    'params' => [
        'param_1' => 'some_value',
        'param_2' => true,
        'param_3' => null,
        'param_4' => null,
        'param_5' => null,
        'param_6' => null,
        'param_7' => null,
        'param_8' => null,
        'param_9' => null,
        'param_10' => null,
    ],
    'lead' => [
        'id' => '1',
        'createdAt' => '2000-01-01T00:00:00+00:00',
        'updatedAt' => '2000-01-01T00:00:00+00:00',
        'currency' => 'USD',
        'project' => [
            'id' => '15',
            'name' => 'S.H.I.E.L.D.',
            'referenceId' => '111',
        ],
        'status' => [
            'id' => '7',
            'name' => 'Approve by operator',
            //Возможные значения: PROCESSING, CANCELED, APPROVED, SHIPPED, DELIVERED, UNDELIVERED, REFUNDED, SPAM, DOUBLE
            'group' => 'APPROVED',
            'referenceId' => '222',
        ],
        //Информация о лиде от ВАШЕГО вебмастера/арбитражника
        'lead' => [
            'webmaster' => [
                'id' => '92',
                'referenceId' => '333',
            ],
            'offer' => [
                'id' => '23',
                'name' => 'Magic tool',
                'referenceId' => '444',
            ],
            'bid' => [
                //Возможные значения: FIXED, PERCENT_FROM_INIT, PERCENT_FROM_TOTAL
                'type' => 'PERCENT_FROM_INIT',
                //В случае использования PERCENT_FROM_INIT или PERCENT_FROM_TOTAL здесь будет указан процент
                'percent' => null,
                //В случае PERCENT_FROM_INIT или PERCENT_FROM_TOTAL значения обоих полей будет null
                'fixed' => [
                    'value' => 20.0,
                    'currency' => 'USD',
                ],
            ],
            'reward' => [
                'value' => 20.0,
                'currency' => 'USD',
            ],
            //Возможные значения: PROCESSING, APPROVED, CANCELED
            'status' => 'APPROVED',
            'holdTo' => '2000-02-01T00:00:00+00:00',
            'finished' => false,
            'externalId' => '589',
            'externalTag' => 'ads',
        ],
        //Информация о перепродаже текущего лида 
        'resale' => [
            'bid' => [
                //Возможные значения: FIXED, PERCENT_FROM_INIT, PERCENT_FROM_TOTAL
                'type' => 'FIXED',
                'percent' => null,
                'fixed' => [
                    'value' => 30.0,
                    'currency' => 'EUR',
                ],
            ],
            'reward' => [
                'value' => 30.0,
                'currency' => 'EUR',
            ],
            'holdTimeInMinutes' => 120,
        ],
        //Набор полей всегда фиксированный, а соответствие передаваемых данных задается внутри CRM
        'data' => [
            'phone_1' => '+78002000600',
            'phone_2' => '+74959379992',
            'humanName_1' => [
                'firstName' => 'Tony',
                'lastName' => 'Stark',
            ],
            'humanName_2' => [
                'firstName' => 'Pepper',
                'lastName' => 'Potts',
            ],
            'address_1' => [
                'postcode' => '10001',
                'region' => 'New York',
                'city' => 'New York City',
                'address_1' => '200 Park Avenue',
                'address_2' => 'Avengers Tower',
                'building' => 'Avengers Tower',
                'apartment' => 'Penthouse 1',
                'countryCode' => 'US',
            ],
            'address_2' => [
                'postcode' => '11901',
                'region' => 'New York',
                'city' => 'Calverton',
                'address_1' => 'Stark Industries Facility',
                'address_2' => 'Avengers Training Center',
                'building' => 'Building A',
                'apartment' => null,
                'countryCode' => 'US',
            ],
            'email_1' => 'tony@starkindustries.com',
            'email_2' => 'pepper@starkindustries.com',
            'uri_1' => 'https://starkindustries.com',
            'uri_2' => 'https://instagram.com/starkindustries',
            //В строках 1 - 10 могут передаваться любые произвольные данные, например комментарий, пожелания клиента, причина
            //отказа ли что угодно иное. В SalesRender вы сами решаете что вы хотите передавать, что нет. При этом рекомендуемый
            //набор данных вы можете указать в файле schema.json, но об этом ниже
            'string_1' => 'T-Mobile',
            'string_2' => '',
            'string_3' => 'value_1',
            'string_4' => 'https://storage.example.com/76cd7f6d-d71b-4263-8e21-b7faa43512e1.pdf',
            'string_5' => '15.15',
            'string_6' => 'https://storage.example.com/7bf5afce-ab55-4fab-88fc-57bbee63fb9e_l.jpg',
            'string_7' => '150',
            'string_8' => 'Hello world',
            'string_9' => 'RU, Postcode here, Region here, City here, Address line 1 here, Address line 2 here, Building here, Apartment here',
            'string_10' => 'Tony Stark',
            'comment' => 'Order was approved by phone call',
        ],
        //Корзина с товарами состоит из единичных товаров и акций (когда несколько товаров объединяют в один по специально ценей)
        'cart' => [
            //Единичные товары
            'items' => [
                [
                    //ИдТовара_НомерВариации
                    'sku' => '1_1',
                    //Название товара
                    'name' => 'Helmet mask',
                    //Название вариации (обычно это цвет, размер и подобное)
                    'property' => 'Gold',
                    'referenceId' => 'sku0387672',
                    'quantity' => 1,
                    'unitPrice' => [
                        'value' => 400.0,
                        'currency' => 'USD',
                    ],
                    'totalPrice' => [
                        'value' => 400.0,
                        'currency' => 'USD',
                    ],
                ],
                [
                    'sku' => '2_2',
                    'name' => 'Jarvis license',
                    'property' => 'Main',
                    'referenceId' => 'sku2356236',
                    'quantity' => 2,
                    'unitPrice' => [
                        'value' => 50.0,
                        'currency' => 'USD',
                    ],
                    'totalPrice' => [
                        'value' => 100.0,
                        'currency' => 'USD',
                    ],
                ],
            ],
            //Акции
            'promotions' => [
                [
                    'id' => '10',
                    'name' => 'Starter kit',
                    'referenceId' => 'prom_10',
                    'quantity' => 1,
                    'unitPrice' => [
                        'value' => 100.0,
                        'currency' => 'USD',
                    ],
                    'totalPrice' => [
                        'value' => 100.0,
                        'currency' => 'USD',
                    ],
                ],
                [
                    'id' => '20',
                    'name' => 'Advanced kit',
                    'referenceId' => 'prom_20',
                    'quantity' => 2,
                    'unitPrice' => [
                        'value' => 150.0,
                        'currency' => 'USD',
                    ],
                    'totalPrice' => [
                        'value' => 300.0,
                        'currency' => 'USD',
                    ],
                ],
            ],
            'totalPrice' => [
                'value' => 1000.0,
                'currency' => 'USD',
            ],
        ],
        'logistic' => [
            //Кодовое имя логистической службы, может быть любым
            'service' => 'FEDEX',
            //Возможные значения: UNREGISTERED, CREATED, REGISTERED, ACCEPTED, PACKED, IN_TRANSIT, ARRIVED, ON_DELIVERY,
            //PENDING, DELIVERED, PAID, RETURNED, RETURNING_TO_SENDER, DELIVERED_TO_SENDER,
            'status' => 'ARRIVED',
            'track' => '12345678900',
            //Стоимость доставки
            'shippingCost' => [
                'value' => 105.0,
                'currency' => 'USD',
            ],
            //Возможные значения: COURIER, PICKUP_POINT, SELF_PICKUP
            'deliveryType' => 'COURIER',
            //Cash-on-delivery
            'cod' => true,
        ],
        'timezone' => [
            'offset' => 'UTC+03:00',
            'offsetInMinutes' => 180,
        ],
        'source' => [
            'ip' => '192.168.1.1',
            'uri' => 'https://example.com/landing',
            'refererUri' => 'https://promo.example.com/landing',
            'utm_source' => 'source_utm',
            'utm_medium' => 'medium_utm',
            'utm_campaign' => 'campaign_utm',
            'utm_content' => 'content_utm',
            'utm_term' => 'term_utm',
            'subid_1' => 'subid_first',
            'subid_2' => 'subid_second',
        ],
    ]
]
```

Для сравнения, вот пример **минимального набора данных**, где вместо данных везде указан `null`. Вы сами настраиваете
передаваемый набор данных внутри SalesRender, при этом невозможно отличить реально отсутствие данных в продаваемом лиде
от отключенных опций внутри SalesRender
```php
[
    'token' => '289ac134c13a449aac56039e6a7d5688',
    'params' => [
        'param_1' => null,
        'param_2' => null,
        'param_3' => null,
        'param_4' => null,
        'param_5' => null,
        'param_6' => null,
        'param_7' => null,
        'param_8' => null,
        'param_9' => null,
        'param_10' => null,
    ],
    'lead' => [
        'id' => '1',
        'createdAt' => null,
        'updatedAt' => null,
        'currency' => 'USD',
        'project' => [
            'id' => null,
            'name' => null,
            'referenceId' => null,
        ],
        'status' => [
            'id' => null,
            'name' => null,
            'group' => null,
            'referenceId' => null,
        ],
        'lead' => [
            'webmaster' => [
                'id' => null,
                'referenceId' => null,
            ],
            'offer' => [
                'id' => null,
                'name' => null,
                'referenceId' => null,
            ],
            'bid' => [
                'type' => null,
                'percent' => null,
                'fixed' => [
                    'value' => null,
                    'currency' => null,
                ],
            ],
            'reward' => [
                'value' => null,
                'currency' => null,
            ],
            'status' => null,
            'holdTo' => null,
            'finished' => null,
            'externalId' => null,
            'externalTag' => null,
        ],
        'resale' => [
            'bid' => [
                'type' => 'FIXED',
                'percent' => null,
                'fixed' => [
                    'value' => 12.0,
                    'currency' => 'EUR',
                ],
            ],
            'reward' => [
                'value' => 12.0,
                'currency' => 'EUR',
            ],
            'holdTimeInMinutes' => 60,
        ],
        'data' => [
            'phone_1' => null,
            'phone_2' => null,
            'humanName_1' => [
                'firstName' => null,
                'lastName' => null,
            ],
            'humanName_2' => [
                'firstName' => null,
                'lastName' => null,
            ],
            'address_1' => [
                'postcode' => null,
                'region' => null,
                'city' => null,
                'address_1' => null,
                'address_2' => null,
                'building' => null,
                'apartment' => null,
                'countryCode' => null,
            ],
            'address_2' => [
                'postcode' => null,
                'region' => null,
                'city' => null,
                'address_1' => null,
                'address_2' => null,
                'building' => null,
                'apartment' => null,
                'countryCode' => null,
            ],
            'email_1' => null,
            'email_2' => null,
            'uri_1' => null,
            'uri_2' => null,
            'string_1' => null,
            'string_2' => null,
            'string_3' => null,
            'string_4' => null,
            'string_5' => null,
            'string_6' => null,
            'string_7' => null,
            'string_8' => null,
            'string_9' => null,
            'string_10' => null,
            'comment' => null,
        ],
        'cart' => [
            'items' => [],
            'promotions' => [],
            'totalPrice' => [
                'value' => null,
                'currency' => null,
            ],
        ],
        'logistic' => [
            'service' => null,
            'status' => null,
            'track' => null,
            'shippingCost' => [
                'value' => null,
                'currency' => null,
            ],
            'deliveryType' => null,
            'cod' => null,
        ],
        'timezone' => [
            'offset' => null,
            'offsetInMinutes' => null,
        ],
        'source' => [
            'ip' => null,
            'uri' => null,
            'refererUri' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'utm_content' => null,
            'utm_term' => null,
            'subid_1' => null,
            'subid_2' => null,
        ],
    ]
]
```
Получив подобный запрос, ваш скрипт `push.php` должен синхронно преобразовать эти данные и отправить их по API в CPA-сеть
или рекламодателю, и вернуть ответ в json **c http-кодом 201** примерно следующего содержания:
```json5
{ 
  //Id заказа в системе рекламодателя или CPA-сети, может быть null
  "externalId": "61568788-6326-409b-a054-31eef4aef47e",

  //Дополнительный идентификатор заказа в системе рекламодателя или CPA-сети, может быть null
  "externalTag": "ExampleCPA",

  //Возможные значения: null или PROCESSING, CANCELED, APPROVED, SHIPPED, DELIVERED, UNDELIVERED, REFUNDED, SPAM, DOUBLE
  "statusGroup": "PROCESSING",
  
  //Возможные значения: null или PROCESSING, APPROVED, CANCELED
  "status": "PROCESSING",  
  
  //Вознаграждение в системе рекламодателя или CPA-сети, может быть null
  "reward": {
    "value": 20.0,
    "currency": "USD"
  }
}
```
Чем больше данных вы сможете вернуть в ответе, тем лучше SalesRender сможет контролировать корректность ставок, статусов
и вашего вознаграждения.

### pull.php
Этот файл отвечает за получение измененных статусов лида из системы рекламодателя или от CPA-сети, их конвертацию и отправку
обратно в SalesRender. Именно на него нужно настроить отправку webhook/postback от рекламодателя или от CPA-сети
с данными о состоянии лида. Полученные через webhook данные должны быть синхронно преобразованы и отправлены обратно в
SalesRender.

Формат передачи данных в вебхуке зависит исключительно от системы рекламодателя или CPA-сети, однако при настройке вебхука
мы используем именно url, полученный [в интерфейсе SalesRender](#технические-аспекты), поэтому при получении данных в
переменной `$_GET` всегда должны быть 4 параметра, из которых формируется url для отправки вебхука в SalesRender:
- `cl` - имя кластера SalesRender
- `cid` - id компании SalesRender
- `lpid` - id покупателя лидов в SalesRender
- `token` - токен доступа

Давайте представим, что мы продаем заказы в выдуманную CPA-сеть ExampleCPA, и из нее нам приходит POST-запрос на наш url
`https://example.com/resale/pull.php?cl=de&cid=10&lpid=20&token=289ac134c13a449aac56039e6a7d5688&currency=USD` примерно 
следующего содержания:
```json5
//Обратите внимание, это вымышленные данные исключительно для того, чтобы показать вам пример кода pull.php
{
  //Id лида в ExampleCPA
  "id": "61568788-6326-409b-a054-31eef4aef47e",
  //Id заказа в SalesRender. В нашем примере ExampleCPA его тоже хранит, но на практике это будет не всегда так
  "orderId": 1,
  //Статус заказа в ExampleCPA. Для примера, возможные значения могут быть: New, Accepted, Rejected, Buyout, Refund
  "orderStatus": "Rejected",
  //Сумма заказа в ExampleCPA (на случай, если вы продаете лиды за процент от итоговой стоимости)
  "orderPrice": 2000,
  //Комментарий к заказу из ExampleCPA. Может быть null или пустой строкой. Обрезается до 255 символов 
  "comment": "No answer from customer",
  //Статус вознаграждения. Для примера, возможные значения: 0 - в холде, 1 - подтвержден, -1 - отказан в выплате 
  "leadStatus": -1,
  //Сумма вознаграждения
  "leadSum": 20.0,
}
//ВЫМЫШЛЕННЫЕ ДАННЫЕ!
```

```php
<?php
//Преобразуем статусы из ExampleCPA в группы статусов SalesRender
$statusGroupMap = [
    'New' => 'PROCESSING',
    'Accepted' => 'APPROVED',
    'Rejected' => 'CANCELED',
    'Buyout' => 'DELIVERED',
    'Refund' => 'REFUNDED',
];
$orderStatus = isset($_POST['orderStatus']) ? $_POST['orderStatus'] : null;
$statusGroup = isset($statusGroupMap[$orderStatus]) ? $statusGroupMap[$orderStatus] : null; 

//Преобразуем статус вознаграждения лида из ExampleCPA в статус вознаграждения в SalesRender
$statusMap = [
    0 => 'PROCESSING',
    1 => 'APPROVED',
    -1 => 'CANCELED',
];
$leadStatus = isset($_POST['leadStatus']) ? $_POST['leadStatus'] : null;
$status = isset($statusMap[$leadStatus]) ? $statusMap[$leadStatus] : null; 

$data = [
    //Token для передачи лидов
    'token' => $_GET['token'],

    //Для того чтобы SalesRender мог найти заказ, у в котором нужно обновить данные, ему необходимо получить либо
    //параметр id (предпочитаемый вариант), либо, если системы рекламодателя или CPA-сети не сохраняет оригинальный id,
    //нужно передать пару externalId + externalTag
    'id' => isset($_POST['orderId']) ? $_POST['orderId'] : null,    
    'externalId' => isset($_POST['id']) ? $_POST['id'] : null,
    'externalTag' => 'ExampleCPA', //может быть null или любым значением, фактически, это дополнительный идентификатор
    
    //Для того чтобы SalesRender мог определить изменения статуса проданного лида, ему необходимо передать как минимум
    //один из этих параметров. Если есть возможность передавать оба, то передавайте оба - так будет лучше для защиты от
    //шейва. Оба параметра могут иметь значение null, но тогда статут лида в SalesRender не изменится
    'statusGroup' => $statusGroup,
    'status' => $status,
    
    //Комментарий к заказу. В нем может быть указана например причина отмены
    'comment' => isset($_POST['comment']) ? $_POST['comment'] : null,   
   
    //Сумма вознаграждения за проданный лид, которую получите вы согласно системе учета рекламодателя или CPA-сети. Этот
    //параметр не обязателен и может быть null, однако он необходим, если выбран способ вознаграждения в виде процента от
    //итоговой стоимости товара. Если есть возможность, всегда передавайте этот параметр. Это поможет оперативно увидеть 
    //расхождения в ставках между SalesRender и системой учета рекламодателя или CPA-сети и легко сделать сверку
    'reward' => [
        'value' => isset($_POST['leadSum']) ? $_POST['leadSum'] : null,
        //Обратите внимание, что в нашем вымышленном примере мы не получаем в теле вебхука информацию о валюте. Нам
        //доступна только сумма. Поэтому мы указываем валюту при настройке вебхука, передав статичный параметр &currency=USD
        //Передавать валюту не обязательно, но желательно. Можно использовать null
        'currency' => isset($_GET['currency']) ? $_GET['currency'] : null,
    ],
];

$url = 'https://' . $_GET['cl'] . '.backend.salesrender.com/companies/' . $_GET['cid'] . '/resale/update/' . $_GET['lpid'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['error' => 'Failed to proxy request']);
    http_response_code(500);
    return;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpCode != 201) {
    echo json_encode(['error' => 'Bad request']);
    http_response_code(400);
    return;
}

echo json_encode(['error' => null]);
http_response_code(200);
return;
```

### schema.json
Данный файл необходим для того, чтобы в визуальном интерфейсе SalesRender вы могли увидеть, какие поля требуется передавать
для данной интеграции, а также настроить некоторые ее параметры. Вы можете посмотреть [пример файла](schema.json), он 
достаточно прост и понятен интуитивно, однако мы все же дадим некоторые комментарии:

- В ключе `mappings` перечислен фиксированный набор полей, которые может передавать SalesRender. Вы, как продавец лидов
сами задаете соответствие данных перечисленных полей к полям внутри SalesRender. Однако, каждая CPA-сеть или система
конкретного рекламодателя может требовать свой набор полей. Чтобы вы не гадали, что и в какие поля передавать, здесь
хранятся подсказки на разных языках. 

- В ключе `params` перечислены 10 параметров, которые могут быть использованы для произвольных целей, и их значения могут либо
передаваться по API в систему рекламодателя или CPA-сети, либо могут менять поведения скрипта `push.php`. Значения заданных
параметров хранятся на стороне SalesRender. Для неиспользуемых параметров используйте `null`. У каждого параметра есть:
  - `type`
    - `bool` - будет отображаться в виде checkbox в интерфейсе SR
    - `string` - будет отображаться в виде строкового поля в интерфейсе SR
    - `{}` - объект, который будет отображаться в виде выпадающего списка на разных языках
  - `label` - заголовок поля в интерфейсе SR на разных языках
  - `default` - значение поля по умолчанию. Ключ `default` не обязателен
  - `required` - `true`/`false`, указывает обязательно ли поле для заполнения. Ключ `required` не обязателен

- В ключе `description` находится описание интеграции на разных языках, которое будет отображаться в интерфейсе SR

**Про языки: первый в списке язык будет использован в качестве языка по умолчанию, если он не совпадает с выбранным языком 
пользователя. Поэтому, мы рекомендуем всегда указывать первым английский язык.**

**Т.к. этот файл запрашивается фронтендом из браузера, для его корректно работы необходимо настроить apache или nginx,
используя приложенные файлы конфигурации**