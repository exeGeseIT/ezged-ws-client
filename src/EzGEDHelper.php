<?php

namespace ExeGeseIT\EzGEDWsClient;

/**
 * Description of EzGEDHelper
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGEDHelper
{
    const DEFAULT_SEARCH_LIMIT = 20;
    
    
    private static function setSearchFilter(string $field, string $value, string $operator): array
    {
        $isOperatorOK = in_array($operator,['=', '>=', '<=', 'like']);
        return [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ];
    }
    
    /**
     * field = value
     * 
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function equalSearchFilter(string $field, string $value): array
    {
        return self::setSearchFilter($field, $value, '=');
    }
    
    /**
     * field >= value
     * 
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function greaterOrEqualSearchFilter(string $field, string $value): array
    {
        return self::setSearchFilter($field, $value, '>=');
    }
    
    /**
     * field <= value
     * 
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function lowerOrEqualSearchFilter(string $field, string $value): array
    {
        return self::setSearchFilter($field, $value, '<=');
    }
    
    /**
     * field like value
     * 
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function likeSearchFilter(string $field, string $value): array
    {
        return self::setSearchFilter($field, $value, 'like');
    }
}
