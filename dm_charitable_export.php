<?php
    const CHARITABLE_STATUSES = ["charitable-pending" => "wc-pending", "charitable-active" => "wc-active", "charitable-completed" => "wc-cancelled"];
    $mydb = new wpdb('wordpress','wordpress','dm_charitable','127.0.0.1');
    // print_r($mydb);
    // print $mydb->last_error;
    $ids = $mydb->get_results("select distinct post_id from wp_postmeta where meta_key = 'donor'");
    $rows = [];
    foreach($ids as $id) {
        $user = new stdClass();
        $post_id = $id->post_id;
        $data = $mydb->get_results("select * from wp_postmeta where post_id=$post_id");
        $post = $mydb->get_results("select * from wp_posts where id=$post_id");
        $user->post_id = $post_id;
        $user->post = $post[0];
        foreach($data as $datum) {
            if ($datum->meta_key === "donor") {
                $user->donor = unserialize($datum->meta_value);
            } elseif ($datum->meta_key === "_donation_log") {
                $user->log = unserialize($datum->meta_value);
                // print "<pre>";
                // print_r($user->log);
                // print "</pre><hr>";
            } elseif ($datum->meta_key === "campaigns") {
                $user->campaigns = unserialize($datum->meta_value);
            } elseif ($datum->meta_key === "donation_period") {
                $user->donation_period = $datum->meta_value;
            } elseif ($datum->meta_key === "donation_interval") {
                $user->donation_interval = $datum->meta_value;
            } elseif ($datum->meta_key === "_gateway_subscription_id") {
                $user->payfast_id = $datum->meta_value;
            }
        }
        $rows[] = $user;
    }
    $users = new stdClass();
    foreach($rows as $row) {
        $email = $row->donor["email"];
        if (!isset($users->{$email})) {
            $users->{$email} = new stdClass();
            $users->{$email}->email = $email;
            $users->{$email}->first_name = $row->donor["first_name"];
            $users->{$email}->last_name = $row->donor["last_name"];
            $users->{$email}->status = CHARITABLE_STATUSES[$row->post->post_status];
            $users->{$email}->type = $row->post->post_type;
            $users->{$email}->post_id = $row->post_id;
        }
        if (isset($row->payfast_id)) {
            $users->{$email}->payfast_id = $row->payfast_id;
        }
        if (isset($row->donation_period)) {
            $users->{$email}->donation_period = $row->donation_period;
        }
        if (isset($row->donation_interval)) {
            $users->{$email}->donation_interval = $row->donation_interval;
        }
        if (isset($row->campaigns)) {
            $users->{$email}->amount = $row->campaigns[0]["amount"];
        }
        if (isset($row->log)) {
            $users->{$email}->log = $row->log;
        }
    }
    print "Rows: " . sizeof($rows) . "; Users: " . sizeof((array)$users);
    print "<table>";
    print "<thead><tr>";
    print "<th>customer_email</th>";
    print "<th>billing_first_name</th>";
    print "<th>billing_last_name</th>";
    print "<th>order_total</th>";
    print "<th>billing_period</th>";
    print "<th>billing_interval</th>";
    print "<th>payment_method_post_meta</th>";
    print "<th>start_date</th>";
    print "<th>customer_note</th>";
    print "<th>Status</th>";
    print "<th>Type</th>";
    print "</tr></thead>";
    print "<tbody>";
    foreach($users as $email => $val) {
        print "<tr>";
        print "<td>" . $val->email . "</td>";
        print "<td>" . $val->first_name . "</td>";
        print "<td>" . $val->last_name . "</td>";
        print "<td>" . $val->amount . "</td>";
        print "<td>" . $val->donation_period . "</td>";
        print "<td>" . $val->donation_interval . "</td>";
        print "<td>" . $val->payfast_id . "</td>";
        print "<td>" . gmdate("Y-m-d\TH:i:s\Z", $val->log[0]["time"]) . "</td>";
        print "<td>Charitable Import - Post ID " . $val->post_id . "</td>";
        print "<td>" . $val->status . "</td>";
        print "<td>" . $val->type . "</td>";
        print "</tr>";
    }
    print "</tbody>";
    print "</table>";
?>
