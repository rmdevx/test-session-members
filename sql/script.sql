delimiter //
CREATE PROCEDURE IF NOT EXISTS correct_duplicates()
BEGIN
	DECLARE done TINYINT DEFAULT 0;
	DECLARE duplicate_id INT;
	DECLARE origin_id INT;
	DECLARE sm_id INT;
	DECLARE sm_client_id INT;
	DECLARE is_orig_id INT;

	DECLARE cur_duplicates CURSOR FOR
        SELECT table1.id AS duplicate_id, table2.id AS origin_id, sm_table.id as sm_id, sm_table.client_id FROM
        (SELECT
             sessions.*
         FROM
             sessions
                 LEFT OUTER JOIN
             (SELECT MIN(id) AS id, start_time, session_configuration_id FROM sessions GROUP BY start_time, session_configuration_id) AS tmp
             ON
                 sessions.id = tmp.id
         WHERE
             tmp.id IS NULL
        ) AS table1
            LEFT JOIN (SELECT MIN(id) AS id, start_time, session_configuration_id FROM sessions GROUP BY start_time, session_configuration_id) AS table2
                      ON table1.start_time = table2.start_time AND table1.session_configuration_id = table2.session_configuration_id
            LEFT JOIN `session_members` AS sm_table ON sm_table.session_id = table1.id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
    START TRANSACTION;


    OPEN cur_duplicates;
    read1_loop: LOOP
            FETCH FROM cur_duplicates INTO duplicate_id, origin_id, sm_id, sm_client_id;
            IF done THEN LEAVE read1_loop; END IF;

            SELECT COUNT(*) INTO is_orig_id FROM `session_members` WHERE session_id = origin_id AND client_id = sm_client_id;

            IF sm_id IS NOT NULL THEN
			    IF is_orig_id = 0 THEN
				    INSERT INTO `session_members`(session_id, client_id) VALUES(origin_id, sm_client_id);
                END IF;
                DELETE FROM `session_members` WHERE id = sm_id;
            END IF;

    END LOOP;
    CLOSE cur_duplicates;

    SET done = 0;
    OPEN cur_duplicates;
    read2_loop: LOOP
		FETCH FROM cur_duplicates INTO duplicate_id, origin_id, sm_id, sm_client_id;
		IF done THEN LEAVE read2_loop; END IF;
        DELETE FROM `sessions` WHERE id = duplicate_id;

    END LOOP;
    CLOSE cur_duplicates;

    COMMIT;
END //
delimiter ;
