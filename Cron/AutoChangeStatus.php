<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Events\Cron;

/**
 * Backend event observer
 */
class AutoChangeStatus
{
    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magebuzz\Events\Model\EventFactory
     */
    private $_eventFactory;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime
     * @param \Magebuzz\Events\Model\EventFactory $eventFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magebuzz\Events\Model\ResourceModel\Event\CollectionFactory $eventFactory
    )
    {
        $this->_date = $date;
        $this->_eventFactory = $eventFactory;
    }

    /**
     * Cron job method to change status in time
     *
     * @return void
     */
    public function execute()
    {
        $events = $this->_eventFactory->create()->getCollection();
        if (count($events)) {
            try {
                foreach ($events as $event) {
                    $now = $this->_date->gmtDate();
                    $startTime = $event->getStartTime();
                    $endTime = $event->getEndTime();
                    if ($endTime < $now) {
                        $status = 'expired';
                    } else if ($startTime < $now && $now < $endTime) {
                        $status = 'happening';
                    } else {
                        $status = 'upcoming';
                    }

                    $data = ['event_id' => $event->getId(), 'progress_status' => $status];
                    $event->setData($data);
                    $event->save();
                }
            } catch (Exception $ex) {
                //do nothing
            }
        }
    }
}
