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

## Технические аспекты
Все процессы и сценарии продажи и перепродажи лидов настраиваются из интерфейса [SalesRender](https://salesrender.com/ru)
в несколько кликов. Однако, у каждого рекламодателя/CPA-сети есть свои особенности API, поэтому для интеграции нужно по
примерам написать всего 2 скрипта.

### push.php
Этот файл отвечает за получение стандартизированных данных из SalesRender, их конвертацию и отправку рекламодателю/CPA-сети
для продажи/перепродажи лида. **Скрипт всегда получает единообразную структуру данных**, поэтому вам не нужно делать в
коде проверки на существование ключа через `isset()`. Структура всегда одинаковая, в каждом ключе либо значение, либо `null`.
**При этом, какие данные отдавать, а какие скрывать (отдавать в виде `null`) вы настраиваете галочками внутри SalesRender.**

Ниже приведен пример **максимально полных данных**, которые получит `push.php` в `$_POST`
```php
[
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
            //Возможные значения: TYPE_FIXED, TYPE_PERCENT_FROM_INIT, TYPE_PERCENT_FROM_TOTAL
            'type' => 'TYPE_PERCENT_FROM_INIT',
            //В случае использования TYPE_PERCENT_FROM_INIT или TYPE_PERCENT_FROM_TOTAL здесь будет указан процент
            'percent' => null,
            //В случае TYPE_PERCENT_FROM_INIT или TYPE_PERCENT_FROM_TOTAL значения обоих полей будет null
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
            //Возможные значения: TYPE_FIXED, TYPE_PERCENT_FROM_INIT, TYPE_PERCENT_FROM_TOTAL
            'type' => 'TYPE_FIXED',
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
        'total' => [
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
        'cost' => [
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
```

Для сравнения, вот пример **минимального набора данных**, где вместо данных везде указан `null`. Вы сами настраиваете 
передаваемый набор данных внутри SalesRender, при этом невозможно отличить реально отсутствие данных в продаваемом лиде
от отключенных опций внутри SalesRender
```php
[
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
            'type' => 'TYPE_FIXED',
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
        'total' => [
            'value' => null,
            'currency' => null,
        ],
    ],
    'logistic' => [
        'service' => null,
        'status' => null,
        'track' => null,
        'cost' => [
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
```
Получив подобный запрос, ваш скрипт `push.php` должен сразу же преобразовать эти данные и отправить их по API в CPA-сеть
или рекламодателю. 