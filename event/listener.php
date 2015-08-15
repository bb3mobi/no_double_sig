<?php

/**
*
* @package Exclude repeat signatures
* @copyright bb3.mobi 2015 (c) Anvar [http://stepnyak.kz)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3mobi\no_double_sig\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	var $post_sig;

	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_modify_post_data'	=> 'viewtopic_modify_post_data',
			'core.viewtopic_modify_post_row'	=> 'viewtopic_modify_post_row',
		);
	}

	// Getting a list of signatures.
	// Check coincidence.
	public function viewtopic_modify_post_data($event)
	{
		$post_list = $event['post_list'];
		$rowset = $event['rowset'];
		$user_cache = $event['user_cache'];

		$post_sig = array();
		for ($i = 0, $end = sizeof($post_list); $i < $end; ++$i)
		{
			// A non-existing rowset only happens if there was no user present for the entered poster_id
			// This could be a broken posts table.
			if (!isset($rowset[$post_list[$i]]))
			{
				continue;
			}

			$row = $rowset[$post_list[$i]];
			$poster_id = $row['user_id'];

			$signature = (isset($post_sig[$poster_id])) ? $post_sig[$poster_id] : '';
			if (!$signature && $user_cache[$poster_id]['sig'] != $signature)
			{
				$post_sig[$poster_id] = $user_cache[$poster_id]['sig'];
				$this->post_sig[$row['post_id']] = true;
			}
		}
	}

	// Deactivating signature
	public function viewtopic_modify_post_row($event)
	{
		$user_poster = $event['user_poster_data'];
		$row = $event['row'];
		if (!isset($this->post_sig[$row['post_id']]))
		{
			$event['post_row'] = array_merge($event['post_row'], array('SIGNATURE' => ''));
		}
	}
}
