<?php
/**
 * Mytunes GEMA report source model
 *
 * TODO: License
 *
 * @category    Que
 * @package     Que_Mytunes
 * @author      Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Model_Sales_Report_Gema extends Mage_Core_Model_Abstract
{
    protected $_data;

    protected $_from;
    protected $_to;

    /**
     * Load the data collection for the currently set report period
     *
     * @return void
     */
    public function loadReport()
    {
        $this->_data = null;

        // get all mytunes order items of the period
        $collection = Mage::getModel('sales/order_item')->getCollection()
            ->joinAttribute('created_at', 'sales_order/created_at', 'order_id')
            ->addAttributeToFilter('product_type', Que_Mytunes_Model_Product_Type::TYPE_MYTUNES)
            ->addAttributeToFilter('created_at', array(
                'from' => $this->_from,
                'to'   => $this->_to,
                'date' => true
            ));

        $this->_data = array(
            'period_start' => $this->_from,
            'period_end' => $this->_to,
            'period_string' => 'TODO: Period Name',
            'artists' => array()
        );

        // parse mytunes options from product_options and fill artists array
        $artists = array();
        $artistsWorks = array();
        foreach ($collection as $item) {
            $options = $item->getProductOptions();
            if (!isset($options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE])) {
                continue;
            }
            $type = $options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE];
            if ($type == Que_Mytunes_Helper_Data::OPTION_MYTUNES_TYPE_ALBUM) {
                $album = Mage::getModel('mytunes/album')->load($options[Que_Mytunes_Helper_Data::OPTION_MYTUNES_ALBUM_ID]);
            }

            // get collection of mytunes links
            $times_downloaded = 0;
            $linkCollection = Mage::getResourceModel('mytunes/link_collection')
                ->addFieldToFilter('order_item_id', $item->getId());
            foreach ($linkCollection as $link) {
                $times_downloaded += (int) $link->getNumberOfDownloadsUsed();
            }

            $reportItem = array(
                'type' => $type,
                'name' => $album->getProduct()->getName(),
                'price' => $album->getPrice(),
                'times_downloaded' => $times_downloaded,
                'times_sold' => 1 // qty is always 1 with downloads
            );

            $artistName = $album->getProduct()->getMytunesArtist();
            $artistId = -1;
            if (!in_array($artistName, $artists)) {
                array_push($artists, $artistName);
                $artistId = count($artists) - 1;
                $artistsWorks[$artistId] = array();
                $this->_data['artists'][$artistId] = array(
                    'artist_name' => $artistName,
                    'items' => array()
                );
            } else {
                $artistId = array_search($artistName, $artists);
            }

            if (!in_array($reportItem['name'], $artistsWorks[$artistId])) {
                // add new work to an artist
                array_push($artistsWorks[$artistId], $reportItem['name']);
                array_push($this->_data['artists'][$artistId]['items'], $reportItem);
            } else {
                // update counters for a work
                $workId = array_search($reportItem['name'], $artistsWorks[$artistId]);
                $this->_data['artists'][$artistId]['items'][$workId]['times_downloaded'] += $reportItem['times_downloaded'];
                $this->_data['artists'][$artistId]['items'][$workId]['times_sold'] += $reportItem['times_sold'];
            }
        } // end foreach

        // var_dump($this->_data); exit();
        // $this->_data = $this->_getTestReportCollection();
    }

    /**
     * Set the start date of the report period.
     *
     * @param string "YYYY-mm-dd"
     *
     * @return Que_Mytunes_Model_Sales_Report_Gema $this
     */
    public function setFrom($from)
    {
        $this->_from = $from;
        return $this;
    }

    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * Set the end date of the report period.
     *
     * @param string "YYYY-mm-dd"
     *
     * @return Que_Mytunes_Model_Sales_Report_Gema $this
     */
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function getTo()
    {
        return $this->_to;
    }

    /**
     * Get all artists for a specified period.
     *
     * @return array(
     *     0 => array(
     *         'artist_name' => string,
     *         'works_count' => int
     *     )
     * )
     */
    public function getArtists()
    {
        if (!isset($this->_data['artists']) || !is_array($this->_data['artists'])) {
            Mage::logException(new Mage_Core_Exception('Error requesting Mytunes GEMA report: Artists not found'));
            return false;
        }

        $artists = array();
        $cnt = 0;
        foreach($this->_data['artists'] as $item) {
            $artist = array(
                'artist_id' => $cnt,
                'artist_name' => $item['artist_name'],
                'works_count' => count($item['items'])
            );
            array_push($artists, $artist);
            $cnt ++;
        }
        return $artists;
    }

    /**
     * Get all works by an artists in a period.
     *
     * @param int artistId
     *
     * @return array(
     *     0 => array(
     *         'type' => 'album' | 'track'
     *         'title' => string,
     *         'price' => float,
     *         'times_sold' => int,
     *         'times_downloaded' => int
     *     )
     * )
     */
    public function getWorksByArtist($artistId)
    {
        if (!isset($this->_data['artists'][$artistId]['items']) || !is_array($this->_data['artists'][$artistId]['items'])) {
            Mage::logException(new Mage_Core_Exception('Error requesting Mytunes GEMA report: Works not found'));
            return false;
        }

        $works = array();
        foreach ($this->_data['artists'][$artistId]['items'] as $item) {
            $work = array(
                'type' => $item['type'],
                'title' => $item['name'],
                'price' => $item['price'],
                'times_sold' => $item['times_sold'],
                'times_downloaded' => $item['times_downloaded']
            );
            array_push($works, $work);
        }
        return $works;
    }

    protected function _getTestReportCollection()
    {
        return array(
            'period_start' => '2011-03-01',
            'period_end' => '2011-03-31',
            'period_string' => 'March 2011',
            'artists' => array(
                0 => array(
                    'artist_name' => 'Miaowmusic',
                    'items' => array(
                        0 => array(
                            'type' => 'album',
                            'name' => 'Miaowmusic',
                            'price' => 7.99,
                            'times_sold' => 23,
                            'times_downloaded' => 42
                        ),
                        1 => array(
                            'type' => 'album',
                            'name' => 'Another break in the mirror',
                            'price' => 9.95,
                            'times_sold' => 17,
                            'times_downloaded' => 24
                        ),
                        2 => array(
                            'type' => 'track',
                            'name' => 'Bubbles',
                            'price' => 0.49,
                            'times_sold' => 12,
                            'times_downloaded' => 15
                        )
                    )
                ),
                1 => array(
                    'artist_name' => 'Björk',
                    'items' => array(
                        0 => array(
                            'type' => 'album',
                            'name' => 'Vespertine',
                            'price' => 5.95,
                            'times_sold' => 19,
                            'times_downloaded' => 25
                        ),
                        1 => array(
                            'type' => 'track',
                            'name' => 'Human Behavior',
                            'price' => 0.79,
                            'times_sold' => 5,
                            'times_downloaded' => 6
                        ),
                    )
                ),
                2 => array(
                    'artist_name' => 'Feist',
                    'items' => array(
                        0 => array(
                            'type' => 'album',
                            'name' => 'Let it die',
                            'price' => 5.95,
                            'times_sold' => 16,
                            'times_downloaded' => 19
                        ),
                        1 => array(
                            'type' => 'track',
                            'name' => 'Gatekeeper',
                            'price' => 0.79,
                            'times_sold' => 11,
                            'times_downloaded' => 11
                        ),
                        2 => array(
                            'type' => 'track',
                            'name' => 'Mushaboom',
                            'price' => 0.79,
                            'times_sold' => 9,
                            'times_downloaded' => 10
                        ),
                        3 => array(
                            'type' => 'track',
                            'name' => 'Killing fields',
                            'price' => 0.79,
                            'times_sold' => 5,
                            'times_downloaded' => 4
                        ),
                    )
                ),
            )
        );
    }
}