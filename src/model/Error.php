<?php

namespace twin\model;

/**
 * Class Error
 *
 * $error = Error::instance($model);
 * $error->setError('id', 'Any message');
 */
class Error
{
    /**
     * Модель, для которой храним ошибки.
     * @var Model
     */
    protected Model $model;

    /**
     * Ошибки атрибутов.
     * @var array
     */
    protected array $errors = [];

    /**
     * Список инстансов.
     * @var array
     */
    protected static array $instances = [];

    /**
     * @param Model $model
     */
    protected function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param Model $model
     * @return static
     */
    public static function instance(Model $model): static
    {
        $id = spl_object_id($model);

        if (!array_key_exists($id, static::$instances)) {
            static::$instances[$id] = new static($model);
        }

        return static::$instances[$id];
    }

    /**
     * Добавить ошибку для атрибута.
     * @param string $attribute - название атрибута
     * @param string $message - текст ошибки
     * @return void
     */
    public function setError(string $attribute, string $message): void
    {
        if ($this->model->hasAttribute($attribute)) {
            $this->errors[$attribute] = $message;
        }
    }

    /**
     * Добавить ошибки для атрибутов.
     * @param array $errors
     * @return void
     */
    public function setErrors(array $errors): void
    {
        foreach ($errors as $attribute => $message) {
            $this->setError($attribute, $message);
        }
    }

    /**
     * Вернуть последнюю ошибку атрибута.
     * @param string $attribute - название атрибута
     * @return string|null - NULL, если ошибки отсутствуют
     */
    public function getError(string $attribute): ?string
    {
        return $this->errors[$attribute] ?? null;
    }

    /**
     * Вернуть массив ошибок атрибутов.
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Имеется ли ошибка у указанного атрибута.
     * @param string $attribute - название атрибута
     * @return bool
     */
    public function hasError(string $attribute): bool
    {
        $message = $this->getError($attribute);
        return !is_null($message);
    }

    /**
     * Имеются ли ошибки у атрибутов.
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Сбросить ошибку у указанного атрибута.
     * @param string $attribute - название атрибута
     * @return void
     */
    public function clearError(string $attribute): void
    {
        if (array_key_exists($attribute, $this->errors)) {
            unset($this->errors[$attribute]);
        }
    }

    /**
     * Сбросить ошибки у указанных атрибутов.
     * @param array $attributes - названия атрибутов, для которых требуется сбросить ошибки (если не указано, то сбросятся все)
     * @return void
     */
    public function clearErrors(array $attributes = []): void
    {
        $attributes = $attributes ?: array_keys($this->errors);

        foreach ($attributes as $attribute) {
            $this->clearError($attribute);
        }
    }
}
