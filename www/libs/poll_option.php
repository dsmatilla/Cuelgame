<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class PollOption
{
    public $id;
    public $option;
    public $votes;
    public $karma;
    public $poll_id;

    public function store()
    {
        if ($this->isEmpty()) {
            return false;
        }

        $this->option = $this->normalize($this->option);

        if ($this->id) {
            $response = $this->update();
        } else {
            $response = $this->insert();
        }

        if ($response === false) {
            return false;
        }

        return true;
    }

    public function isEmpty()
    {
        return empty($this->option) || empty($this->poll_id);
    }

    private function insert()
    {
        global $db;

        $response = $db->query(str_replace("\n", ' ', '
            INSERT INTO `polls_options`
            SET
                `option` = "'.$this->option.'",
                `poll_id` = '.($this->poll_id ?: 'NULL').';
        '));

        if (empty($response)) {
            return false;
        }

        $this->id = $db->insert_id;

        return true;
    }

    private function update()
    {
        global $db;

        $response = $db->query(str_replace("\n", ' ', '
            UPDATE `polls_options`
            SET `option` = "'.$this->option.'"
            WHERE `id` = "'.(int)$this->id.'"
            LIMIT 1;
        '));

        return $response ? true : false;
    }

    private function normalize($value)
    {
        return clean_lines(clear_whitespace($value));
    }

    public static function selectFromPollIds(array $poll_ids)
    {
        global $db;

        $poll_ids = array_filter(array_unique(array_map('intval', $poll_ids)));

        return $db->object_iterator(str_replace("\n", ' ', '
            SELECT *
            FROM `polls_options`
            WHERE `poll_id` IN ('.implode(',', $poll_ids).');
        '), 'PollOption');
    }
}
