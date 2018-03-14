<?php

/**
 * Validate data like phone, e-mail, name
 *
 * @author grinderspro <grinderspro@gmail.com>
 * @copyright 2013, Grigorij Miroshnichenko
 */

require_once('Simpla.php');

class Validator extends Simpla
{
    /**
     * Конвертирует номер телефона в эталонный формат.
     * За эталонный формат считаем 380ZZXXXXXXX,
     * где ZZ - код оператора, XXXXXXX - телефонный номер
     *
     * Логика такова: вытаскиваем последние 9 символов из номера -
     * код (2) + телефон (7). Затем склеиваем с кодом страны.
     *
     * @uses Validator::phone_filter()
     *
     * @param string $phone Номер телефона в произвольном формате
     * @param string $code Код страны. По умолчанию - 380
     *
     * @return string
     */
    public function convert_phone_to_right_format($phone, $code = '380')
    {
        // Если номер телефона невалидный (в т.ч. российский), возвращаем как есть.
        if (!$this->phone_filter($phone)) {
            return $phone;
        }

        $_phone = substr($phone, -9, 9);

        return $code . $_phone;
    }

    /**
     * Проверяет номера телефонов на валидность.
     *
     * @param string $phone Номер телефона
     * @param bool $russian Флаг необходимости проверки российских номеров.
     *                        По умолчанию проверяются все телефоны.
     *
     * @return bool
     */
    public function phone_filter($phone, $russian = true)
    {
        if (empty($phone)) {
            return false;
        }

        $count = strlen($phone);

        /*
         * Если строка меньше 9 символов: оператор(2) + телефон(7)
         * или длиннее 13 символов: "+"(1) + код страны(3) + оператор(2) + телефон(7),
         * то телефон не валидный
         *
         * "< 10" по той причине, что телефон обычно указывается как 093.. (0 + оператор),
         * а если будет указано ровно 9 символов, то уже есть сомнения, что это
         * валидный телефон
         */
        if ($count < 10 || $count > 13) {
            return false;
        }

        /*
         * Иногда могут встречаться российские номера.
         * Проверка может быть отключена установкой второго параметра в false
         */
        if ($russian && (preg_match('/^89\d+/', $phone) || preg_match('/^79\d+/', $phone))) {
            return false;
        }

        return true;
    }


    public function check_phone($phone)
    {
        if (!preg_match("|^[+]?[\d-()]*$|", $phone))
            return false;

        return true;
    }


    public function check_email($email)
    {
        if (!preg_match("|^[-0-9a-z_.]+@[0-9a-z_^\.]+\.[a-z]{2,6}$|i", $email))
            return false;

        return true;
    }


    public function check_name($name)
    {
        if (!preg_match("|^[0-9a-z_-]*$|i", $name))
            return false;

        return true;
    }

    public function check_rname($name)
    {
        if (!preg_match("|^[ 0-9a-я_-]*$|i", $name))
            return false;

        return true;
    }
}

