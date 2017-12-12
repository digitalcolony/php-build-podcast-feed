<?php
    header('Content-Type: application/xml; charset=utf-8');
    $site_url = "https://example.com";

    // === Generic feed options
    $feed_title = "Super Cool Podcast";
    $feed_link = "https://example.com/podcast/";
    $feed_description = "The Super Cool Podcast talks about examples. A new show every Tuesday.";
    $feed_copyright = "2017-2018 Super Cool Podcast";
    $feed_keywords = "Fun, Examples, RSS, GitHub";
    $feed_subtitle = "For example...";
    // How often feed readers check for new material (in seconds) -- mostly ignored by readers
    $feed_ttl = 60 * 60 * 24;
    $feed_lang = "en-us";
    
    // $feed_pub_date will always be 8am of the current day 
    $today = date("Y-m-d");
    $tz = new DateTimeZone('America/New_York');
    $feed_pub_date = new DateTime($today, $tz);
    $feed_pub_date->modify('+8 hours');
    $feed_pub_date_formatted = $feed_pub_date->format("r");

    // === iTunes-specific feed options

    $feed_author = "Johnny Cool";
    $feed_email = "cool@example.com (Johnny Cool)";
    $feed_image = "http://example.com/images/example1400.jpg";
    $feed_explicit = "yes";
    // TODO: Can not figure out how to validate categories with ampersands, so using Food. Works!
    $feed_category = "Comedy";    
    $feed_subcategory = "Food";

    // Uncomment this line for browser debugging 
    /*echo '<?xml version="1.0" encoding="utf-8" ?>'; */
?>
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
    <channel>
        <title><?php echo $feed_title; ?></title>
        <link><?php echo $site_url; ?></link>
        <image>
            <url><?php echo $feed_image; ?></url>
            <title><?php echo $feed_title; ?></title>
            <link><?php echo $site_url; ?></link>
        </image>
        <description>
            <?php echo $feed_description; ?>
        </description>
        <language><?php echo $feed_lang; ?></language>
        <copyright><?php echo $feed_copyright; ?></copyright>
        <atom:link href="<?php echo $feed_link; ?>" rel="self" type="application/rss+xml"/>
        <lastBuildDate><?php echo $feed_pub_date_formatted; ?></lastBuildDate>

        <itunes:author><?php echo $feed_author; ?></itunes:author>
        <itunes:summary><?php echo $feed_description; ?></itunes:summary>
        <itunes:subtitle><?php echo $feed_subtitle; ?></itunes:subtitle>
        <itunes:owner>
            <itunes:name><?php echo $feed_author; ?></itunes:name>
            <itunes:email><?php echo $feed_email; ?></itunes:email>
        </itunes:owner>
        <itunes:explicit><?php echo $feed_explicit; ?></itunes:explicit>
        <itunes:keywords><?php echo $feed_keywords; ?></itunes:keywords>
        <itunes:image href="<?php echo $feed_image; ?>" />                
        <itunes:category text="<?php echo $feed_category; ?>"/>
        <pubDate><?php echo "Fri, 28 Apr 2017 12:34:00 EDT"; ?></pubDate>               
        <category><?php echo $feed_category; ?></category>
        <ttl><?php echo $feed_ttl; ?></ttl>

        <?php
            date_default_timezone_set('America/New_York');
	        // connect to DB
	        include("inc/connect.php");

            //NOTE: duration is seconds, fileSize is bytes 
            $sql = "SELECT title, duration, mp3url, fileSize, notes, releaseTime
                FROM whatevertable
                WHERE releaseTime < Now()
                ORDER BY releaseTime DESC";

            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {                    
                    $file_title = htmlentities($row["title"]);
                    $file_url = $row["mp3url"];
                    // iTunes does not support all HTTPS certificates - use this line if you have issues 
                    $file_url = str_replace('https','http',$file_url);
                    $file_author = $feed_author;
                    $file_duration = $row["duration"]; 
                    $file_description = htmlentities($row["notes"]);                                      
                    $pub_date = date("r", strtotime($row["releaseTime"])); // "Mon, 24 Apr 2017 12:34:00 EDT"
                    $file_size = $row["fileSize"];
                                                 
        ?>

                    <item>
                        <title><![CDATA[<?php echo $file_title; ?>]]></title>
                        <link>
                            <?php echo $file_url; ?>
                        </link>
                        <pubDate><?php echo $pub_date; ?></pubDate>
                        <description>
                            <![CDATA[<?php echo $file_description; ?>]]>
                        </description>
                        <enclosure url="<?php echo $file_url; ?>" length="<?php echo $file_size; ?>" type="audio/mpeg"/>                        
                        <guid>
                            <?php echo $file_url; ?>
                        </guid>
                        <itunes:duration><?php echo $file_duration; ?></itunes:duration>
                        <itunes:summary><![CDATA[
                            <?php echo $file_description; ?>
                        ]]></itunes:summary>
                        <itunes:image href="<?php echo $feed_image; ?>"/>
                        <itunes:keywords>
                            <?php echo $feed_keywords; ?> 
                        </itunes:keywords>
                        <itunes:explicit><?php echo $feed_explicit; ?></itunes:explicit>                        
                    </item>                    

        <?php
            }
        }        
        ?>

    </channel>
</rss>