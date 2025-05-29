<?php

namespace twin\response;

use twin\common\Component;
use twin\helper\Header;

class Response extends Component
{
    /**
     * Список заголовков, которые будут зарегистрированы.
     * @var array
     */
    protected array $headers = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        $this->registerHeaders();
    }

    /**
     * Преобразовать тело документа и зарегистрировать заголовки.
     * @param mixed $data
     * @return string
     */
    public function run(mixed $data): string
    {
        return (string)$data;
    }

    /**
     * Зарегистрировать заголовки.
     * @return void
     */
    protected function registerHeaders(): void
    {
        $header = $this->getHeader();
        $header->clear();
        $header->addMultiple($this->headers);
    }

    /**
     * Инстанцировать хелпер для работы с заголовками.
     * @return Header
     */
    protected function getHeader(): Header
    {
        return new Header;
    }
}
