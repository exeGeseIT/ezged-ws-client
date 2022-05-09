<?php

namespace ExeGeseIT\EzGEDWsClient;

use ExeGeseIT\EzGEDWsClient\Exception\InvalidArgumentException;

/**
 * Description of EzGEDHelper
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGEDHelper
{
    const DEFAULT_SEARCH_LIMIT = 20;
    const DEFAULT_UPLOAD_TOKEN = 'c3e335143464469a1ca542c21147f913';
    
    const DEFAULT_JOBSTATUS_POOLING_WAITTIME = 2;
    
    private const OPERATORS = ['=', '>=', '<=', 'like', '<>', 'not like'];
    private const DIRECTIONS = ['ASC', 'DESC'];
    
    /**
     * Le job est prêt et en attente d'être traité par le serveur de travaux
     */
    const JOB_STATUS_PENDING = 'PENDING';

    /**
     * Le job est en file, i.e sélectionné par le serveur de travaux pour s'exécuter
     */
    const JOB_STATUS_QUEUED = 'QUEUED';

    /**
     * Le job est terminé. Il sera purgé automatiquement
     */
    const JOB_STATUS_ENDED_PURGEABLE = 'PURGEABLE';

    /**
     * Le job est terminé. Il ne sera pas purgé automatiquement
     */
    const JOB_STATUS_ENDED_NOT_PURGEABLE = 'NOTPURGEABLE';

    /**
     * Le job est en erreur. Des tentatives d'exécution seront faites périodiquement.
     * (par défaut toutes les 3 mins)
     */
    const JOB_STATUS_ERROR = 'ERROR';

    /**
     * Le job a rencontré une erreur critique.
     *   => il est désactivé ==>> pas de ré-exécution périodique.
     * Ce status est également utilisé pour mettre un job en attente de l'indexation complète de son document (Centre d'indexation)
     */
    const JOB_STATUS_CRITICAL = 'CRITICAL';
    
    
    private static function setSearchFilter(string $field, string $value, string $operator): array
    {
        return [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ];
    }
    
    private static function setSearchDirection(string $field, string $direction): array
    {
        return [
            'field' => $field,
            'direction' => $direction,
        ];
    }
    
    /**
     * 
     * @param string $direction
     * @param bool $throwIfInvalid if true, throw an InvalidArgumentException if invalid
     * @return bool
     * 
     * @throws InvalidArgumentException
     */
    public static function isSearchDirection(string $direction, bool $throwIfInvalid = false): bool
    {
        if ( $throwIfInvalid && !in_array($direction, self::DIRECTIONS) ) {
            throw new InvalidArgumentException(sprintf('%s: valid direction are (%), received: \"%s\"', 
                __METHOD__, implode(', ', self::DIRECTIONS), $direction
            ));
        }
        return in_array($direction, self::DIRECTIONS);
    }
    
    /**
     *  field ASC
     * @param string $field
     * @return array
     */
    public static function orderASC(string $field): array
    {
        return self::setSearchDirection($field, 'ASC');
    }
    
    /**
     *  field DESC
     * @param string $field
     * @return array
     */
    public static function orderDESC(string $field): array
    {
        return self::setSearchDirection($field, 'DESC');
    }


    /**
     * 
     * @param string $operator
     * @param bool $throwIfInvalid if true, throw an InvalidArgumentException if invalid
     * @return bool
     * 
     * @throws InvalidArgumentException
     */
    public static function isSearchOperator(string $operator, bool $throwIfInvalid = false): bool
    {
        if ( $throwIfInvalid && !in_array($operator, self::OPERATORS) ) {
            throw new InvalidArgumentException(sprintf('%s: valid operators are (%), received: \"%s\"', 
                __METHOD__, implode(', ', self::OPERATORS), $operator
            ));
        }
        return in_array($operator, self::OPERATORS);
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
     * field <> value
     * 
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function notEqualSearchFilter(string $field, string $value): array
    {
        return self::setSearchFilter($field, $value, '<>');
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
    
    /**
     * field not like value
     * 
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function notLikeSearchFilter(string $field, string $value): array
    {
        return self::setSearchFilter($field, $value, 'not like');
    }
}
