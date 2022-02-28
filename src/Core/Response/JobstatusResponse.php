<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;
use ExeGeseIT\EzGEDWsClient\Core\EzJobstatusInterface;
use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\ParameterBag;
use ExeGeseIT\EzGEDWsClient\EzGEDHelper;

/**
 * Description of PerimeterResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class JobstatusResponse extends EzGEDResponseAbstract implements EzJobstatusInterface
{
    
    protected static $STATE_IS_SUCCEED = [EzGEDHelper::JOB_STATUS_ENDED_PURGEABLE, EzGEDHelper::JOB_STATUS_ENDED_NOT_PURGEABLE];
    protected static $STATE_IS_FAILED = [EzGEDHelper::JOB_STATUS_ERROR, EzGEDHelper::JOB_STATUS_CRITICAL];
    
    private ?int $jobId = null;
    private ?int $statusId = null;
    
    public function getJobId(): ?int
    {
        return $this->jobId;
    }
    
    public function getJobStatus(): ?int
    {
        return $this->statusId;
    }
    
    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        if ( !$this->content->has('JOBQUEUE_STATUS') ) {
            return null;
        }
        
        switch ( $this->content->get('JOBQUEUE_STATUS') ) {
            case self::JOBSTATUS_PENDING:
                return EzGEDHelper::JOB_STATUS_PENDING;
            case self::JOBSTATUS_QUEUED:
                return EzGEDHelper::JOB_STATUS_QUEUED;
            case self::JOBSTATUS_ENDED_PURGEABLE:
                return EzGEDHelper::JOB_STATUS_ENDED_PURGEABLE;
            case self::JOBSTATUS_ENDED_NOT_PURGEABLE:
                return EzGEDHelper::JOB_STATUS_ENDED_NOT_PURGEABLE;
            case self::JOBSTATUS_ERROR:
                return EzGEDHelper::JOB_STATUS_ERROR;
            case self::JOBSTATUS_CRITICAL:
                return EzGEDHelper::JOB_STATUS_CRITICAL;
            default:
                return null;
        }
    }

    /**
     * @return bool
     */
    public function isJobSucceed(): bool
    {
        return in_array($this->getStatus(), self::$STATE_IS_SUCCEED);
    }

    /**
     * @return bool
     */
    public function isJobFailed(): bool
    {
        return in_array($this->getStatus(), self::$STATE_IS_FAILED);
    }

    /**
     * @return bool
     */
    public function onFinalState(): bool
    {
        $status = $this->getStatus();
        return isset($status) && !in_array($status,[EzGEDHelper::JOB_STATUS_PENDING, EzGEDHelper::JOB_STATUS_QUEUED]);
    }

            
    protected function initialize(array $data): void
    {
        $rows = array_key_exists('rows', $data) ? $data['rows'][0] : null;
        if ( $rows ) {
            $this->jobId = $rows['JOBQUEUE_ID'];
            $this->statusId = isset($rows['JOBQUEUE_STATUS']) ? $rows['JOBQUEUE_STATUS'] : null;
            unset($rows['JOBQUEUE_ID']);
            
            if ( !empty($rows) ) {
                $this->content = new ParameterBag($rows);
            }
        }
    }
}
