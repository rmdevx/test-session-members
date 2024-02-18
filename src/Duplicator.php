<?php

namespace Rmdevx\TestSessionMembers;

class Duplicator
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function correctMemberRecords(): void
    {
        $pdo = $this->db->getPdo();
        $db = $this->db;

        try {
            $pdo->beginTransaction();


            $result = $db->query("SELECT * FROM `sessions` ORDER BY id");

            $arItems = $this->preparingSessions($result);

            foreach ($this->duplicateSessions($arItems) as $sessionInfo) {

                $arMemberItems = $db->query("SELECT * FROM `session_members` WHERE session_id = :session_id",
                    ["session_id" => $sessionInfo["duplicate_id"] ]);

                foreach ($arMemberItems as $item) {
                    $result = $db->query("SELECT * FROM `session_members` WHERE client_id = :client_id AND session_id = :session_id",
                        [
                            "session_id" => $sessionInfo["original_id"],
                            "client_id" => $item["client_id"]
                        ]);

                    if (count($result) == 0) {
                        $db->execute("INSERT INTO `session_members`(session_id, client_id) VALUES(:session_id, :client_id)",
                            [
                                "session_id" => $sessionInfo["original_id"],
                                "client_id" => $item["client_id"]
                            ]);
                    }

                    $db->execute("DELETE FROM `session_members` WHERE id = :id", ["id" => $item["id"]]);
                }

                $db->execute("DELETE FROM `sessions` WHERE id = :id", ["id" => $sessionInfo["duplicate_id"]]);
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

    private function duplicateSessions(array $arGroupedItems): iterable
    {
        foreach ($arGroupedItems as $arItems) {
            foreach ($arItems as $arSessions) {
                if (count($arSessions) > 1) {

                    // Сортируем массив по id, чтобы не зависеть от порядка выборки из БД
                    usort($arSessions, function ($a, $b) {
                        return ($a["id"] > $b["id"]) ? 1 : -1;
                    });

                    for ($i = 1; $i < count($arSessions); $i++) {
                        yield [
                            "original_id" => $arSessions[0]["id"],
                            "duplicate_id" => $arSessions[$i]["id"]
                        ];
                    }
                }
            }
        }
    }

    private function preparingSessions(array $origSessions): array
    {
        $resSessions = [];
        foreach ($origSessions as $item) {

            $key_id = $item["session_configuration_id"];

            if (!isset($resSessions[$key_id])) {
                $resSessions[$key_id] = [];
            }

            $date = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $item["start_time"]);
            $key_time = $date->getTimestamp();

            if (!isset($resSessions[$key_id][$key_time])) {
                $resSessions[$key_id][$key_time] = [];
            }

            $resSessions[$key_id][$key_time][] = $item;
        }

        return $resSessions;
    }
}