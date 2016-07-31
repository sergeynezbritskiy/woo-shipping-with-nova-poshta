<?php

namespace plugins\NovaPoshta\classes;

use Exception;
use plugins\NovaPoshta\classes\base\Base;
use WC_Order;

/**
 * Class DocumentGenerator
 * @package plugins\NovaPoshta\classes
 * @property array defaultSender
 * @property array defaultRecipient
 * @property array defaultParams
 * @property array sender
 */
class DocumentGenerator extends Base
{
    const ACTION_GENERATE_DOCUMENT = 'generate_nova_poshta_document';

    public function init()
    {
        add_action('add_meta_boxes', array($this, 'initialiseMetabox'));
//        add_action('admin_enqueue_scripts', array($this, 'scripts'));
//        add_action('admin_print_styles', array($this, 'styles'));
//        add_action('wp_ajax_' . self::ACTION_GENERATE_DOCUMENT, array($this, 'ajaxGenerateDocument'));
    }

    /**
     * initialises metabox on WooCommerce order pages
     */
    public function initialiseMetabox()
    {
        add_meta_box('document_generator_metabox', 'Генератор экспресс накладной', array($this, 'displayMetabox'), 'shop_order', 'side', 'high');
    }

    /**
     * displays metabox content on WooCommerce order pages
     */
    public function displayMetabox()
    {
        require_once NOVA_POSHTA_SHIPPING_TEMPLATES_DIR . 'document-generator-metabox.php';
    }

    public function styles()
    {
        wp_register_style(
            'nova_poshta_document_generator_style',
            NOVA_POSHTA_SHIPPING_PLUGIN_URL . 'assets/css/document-generator.css',
            array(),
            filemtime(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'assets/css/document-generator.css')
        );
        wp_enqueue_style('nova_poshta_document_generator_style');
    }

    public function scripts()
    {
        wp_register_script(
            'nova_poshta_document_generator',
            NOVA_POSHTA_SHIPPING_PLUGIN_URL . 'assets/js/document-generator.js',
            array('jquery'),
            filemtime(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'assets/js/document-generator.js')
        );

        wp_localize_script('nova_poshta_document_generator', 'NovaPoshta', array(
            'ajaxUrl' => admin_url('admin-ajax.php', 'relative'),
            'generateDocumentAction' => DocumentGenerator::ACTION_GENERATE_DOCUMENT
        ));

        wp_enqueue_script('nova_poshta_document_generator');
    }


    public function ajaxGenerateDocument()
    {
        $data = $_POST['nova_poshta'];
        $sender = $this->sender;
        $orderId = $data['order_id'];
        $recipientRegion = new Region($data['billing_state']);
        $recipientCity = new City($data['billing_city']);
        $recipientWarehouse = new Warehouse($data['billing_address_1']);

        $recipient = ArrayHelper::merge($this->defaultRecipient, array(
            'FirstName' => $data['billing_first_name'],
            'MiddleName' => '',
            'LastName' => $data['billing_last_name'],
            'Phone' => $data['billing_phone'],
            'Region' => $recipientRegion->Description,
            'City' => $recipientCity->Description,
            'Warehouse' => $recipientWarehouse->Description,
        ));

        $redelivery = $this->calculateRedelivery($orderId);
        $params = ArrayHelper::merge($this->defaultParams, array(
            'Cost' => $redelivery,
            // Кол-во мест
            'SeatsAmount' => '1',
            // Описание груза
            'Description' => $data['items_description'],
            // Тип доставки, дополнительно - getCargoTypes
            'CargoType' => 'Cargo',
            // Вес груза
            'Weight' => $data['weight'],
            // Объем груза в куб.м.
            'VolumeGeneral' => '',
            // Обратная доставка
            'BackwardDeliveryData' => array(
                array(
                    // Кто оплачивает обратную доставку
                    'PayerType' => 'Recipient',
                    // Тип доставки
                    'CargoType' => 'Money',
                    // Значение обратной доставки
                    'RedeliveryString' => $redelivery,
                )
            )
        ));

        $result = true;
        $errors = '';
        try {
            $response = NP()->api->newInternetDocument($sender, $recipient, $params);
            $document = new Document($orderId);
            $document->update(array_shift($response));
            $message = sprintf("Декларация успешно создана. Скачать декларацию можно по ссылке <strong><a href='{$document->getLink()}' target='_blank'>Скачать декларацию</a></strong>");
        } catch (Exception $e) {
            $result = false;
            $message = "Во время генерации документа произошли ошибки. " . $e->getMessage();
            $errors = $e->getTraceAsString();
        }
        echo json_encode(array(
            'result' => $result,
            'message' => $message,
            'errors' => $errors,
        ));
        exit;
    }

    /**
     * @var self
     */
    private static $_instance;

    public static function instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    /**
     * @return array
     */
    protected function getSender()
    {
        $options = get_site_option('woocommerce_nova_poshta_shipping_method_settings');
        $currentCity = $options['city2'];
        $currentWarehouse = $options['warehouse'];
        $np = NP()->api;
        // Перед генерированием ЭН необходимо получить данные отправителя
        // Получение всех отправителей
        $senderInfo = $np->getCounterparties('Sender', 1, '', $currentCity);
        // Выбор отправителя в конкретном городе (в данном случае - в первом попавшемся)
        $sender = $this->getCurrentSender($senderInfo, $currentCity);
        // Информация о складе отправителя
        $senderWarehouses = $np->getWarehouses($sender['City']);
        $this->sender = ArrayHelper::merge($this->defaultSender, array(
            // Данные пользователя
            'FirstName' => $sender['FirstName'],
            'MiddleName' => $sender['MiddleName'],
            'LastName' => $sender['LastName'],
            // Вместо FirstName, MiddleName, LastName можно ввести зарегистрированные ФИО отправителя или название фирмы для юрлиц
            // (можно получить, вызвав метод getCounterparties('Sender', 1, '', ''))
            // 'Description' => $sender['Description'],
            // Необязательное поле, в случае отсутствия будет использоваться из данных контакта
            // 'Phone' => '0631112233',
            // Город отправления
            // 'City' => 'Белгород-Днестровский',
            // Область отправления
            // 'Region' => 'Одесская',
            'CitySender' => $sender['City'],
            // Отделение отправления по ID (в данном случае - в первом попавшемся)
            'SenderAddress' => $this->getSenderWarehouse($senderWarehouses, $currentWarehouse),
            // Отделение отправления по адресу
            // 'Warehouse' => $senderWarehouses['data'][0]['DescriptionRu'],
        ));
        return $this->sender;
    }

    private function getCurrentSender($senderInfo, $currentCity)
    {
        foreach ($senderInfo as $sender) {
            if ($sender['City'] == $currentCity) {
                return $sender;
            }
        }
        return array_shift($senderInfo);
    }

    /**
     * @return array
     */
    protected function getDefaultSender()
    {
        $this->defaultSender = array(
            'Sender' => '',
            'Description' => '',
            'Phone' => '',
        );
        return $this->defaultSender;
    }

    /**
     * @return array
     */
    protected function getDefaultRecipient()
    {
        $this->defaultRecipient = array(
            'FirstName' => '',
            'MiddleName' => '',
            'LastName' => '',
            'Phone' => '',
            'Region' => '',
            'City' => '',
            'Warehouse' => '',
            'CounterpartyType' => 'PrivatePerson',
            'CityRecipient' => '',
            'RecipientAddress' => '',
            'Recipient' => '',
        );
        return $this->defaultRecipient;
    }

    /**
     * @return array
     */
    protected function getDefaultParams()
    {
        $this->defaultParams = array(
            // Дата отправления
            'DateTime' => date('d.m.Y'),
            // Тип доставки, дополнительно - getServiceTypes()
            'ServiceType' => 'WarehouseWarehouse',
            // Тип оплаты, дополнительно - getPaymentForms()
            'PaymentMethod' => 'Cash',
            // Кто оплачивает за доставку
            'PayerType' => 'Recipient',
            // Стоимость груза в грн
            'Cost' => '',
            // Кол-во мест
            'SeatsAmount' => '1',
            // Описание груза
            'Description' => '',
            // Тип доставки, дополнительно - getCargoTypes
            'CargoType' => 'Cargo',
            // Вес груза
            'Weight' => '',
            // Объем груза в куб.м.
            'VolumeGeneral' => '',
        );
        return $this->defaultParams;
    }

    private function __clone()
    {
    }

    /**
     * @param array $warehouses
     * @param string $currentWarehouse
     * @return string
     */
    private function getSenderWarehouse($warehouses, $currentWarehouse)
    {
        foreach ($warehouses as $warehouse) {
            if ($warehouse['Ref'] == $currentWarehouse) {
                return $currentWarehouse;
            }
        }
        return $warehouses[0]['Ref'];
    }

    /**
     * @param int $orderId
     * @return float
     */
    public function calculateRedelivery($orderId)
    {
        $order = new WC_Order($orderId);
        return $order->get_total() - $order->get_total_shipping();
    }
}