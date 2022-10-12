<?php
// How often to poll, in microseconds (1,000,000 μs equals 1 s)
define('MESSAGE_POLL_MICROSECONDS', 500000);
 
// How long to keep the Long Poll open, in seconds
define('MESSAGE_TIMEOUT_SECONDS', 30);
 
// Timeout padding in seconds, to avoid a premature timeout in case the last call in the loop is taking a while
define('MESSAGE_TIMEOUT_SECONDS_BUFFER', 5);
 
// Hold on to any session data you might need now, since we need to close the session before entering the sleep loop
$user_id = $_SESSION['id'];
 
// Close the session prematurely to avoid usleep() from locking other requests
session_write_close();
 
// Automatically die after timeout (plus buffer)
set_time_limit(MESSAGE_TIMEOUT_SECONDS+MESSAGE_TIMEOUT_SECONDS_BUFFER);
 
// Counter to manually keep track of time elapsed (PHP's set_time_limit() is unrealiable while sleeping)
$counter = MESSAGE_TIMEOUT_SECONDS;
 
// Poll for messages and hang if nothing is found, until the timeout is exhausted
while($counter > 0)
{
    // Check for new data (not illustrated)
    if($data = getNewData($user_id))
    {
        // Break out of while loop if new data is populated
        break;
    }
    else
    {
        // Otherwise, sleep for the specified time, after which the loop runs again
        usleep(MESSAGE_POLL_MICROSECONDS);
 
        // Decrement seconds from counter (the interval was set in μs, see above)
        $counter -= MESSAGE_POLL_MICROSECONDS / 1000000;
    }
}
 
// If we've made it this far, we've either timed out or have some data to deliver to the client
if(isset($data))
{
    // Send data to client; you may want to precede it by a mime type definition header, eg. in the case of JSON or XML
    echo $data;
}
?>