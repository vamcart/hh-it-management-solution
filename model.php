<?php

/**
 * Return list of users.
 */
function get_users($conn)
{
    $statement = $conn->prepare('
        SELECT users.id, users.name
        FROM   users
            INNER JOIN user_accounts
                    ON user_accounts.user_id = users.id
            INNER JOIN transactions
                    ON ( transactions.account_from = user_accounts.id )
                        OR ( transactions.account_to = user_accounts.id )
        GROUP  BY users.id; 
    ');

    try {
        $statement->execute();
    } catch (PDOException $e) {
        echo "Statement failed: " . $e->getMessage();
        return false;
    }

    $users = array();
    while ($row = $statement->fetch()) {
        $users[$row['id']] = $row['name'];
    }

    return $users;
}

/**
 * Return transactions balances of given user.
 */
function get_user_transactions_balances($user_id, $conn)
{
    $accounts = implode(",", get_user_accounts($user_id, $conn));
    $statement = $conn->prepare("
        SELECT Strftime('%m', trdate) AS month,
            Sum(CASE
                    WHEN account_from IN ( " . $accounts . " ) THEN amount
                    ELSE NULL
                END)               AS out_total,
            Sum(CASE
                    WHEN account_to IN ( " . $accounts . " ) THEN amount
                    ELSE NULL
                END)               AS in_total
        FROM   transactions
        GROUP  BY month
        ORDER  BY month ASC; 
    ");

    try {
        $statement->execute();
    } catch (PDOException $e) {
        echo "Statement failed: " . $e->getMessage();
        return false;
    }

    $transactions = array();
    while ($row = $statement->fetch()) {
        $transactions[$row['month']] = $row['in_total'] - $row['out_total'];
    }
    return $transactions;
}

/**
 * Return accounts of given user.
 */
function get_user_accounts($user_id, $conn)
{
    $statement = $conn->prepare("
        SELECT id
        FROM   user_accounts
        where user_id = '" . $user_id . "'
        ORDER  BY id ASC; 
    ");

    try {
        $statement->execute();
    } catch (PDOException $e) {
        echo "Statement failed: " . $e->getMessage();
        return false;
    }

    $accounts = array();
    while ($row = $statement->fetch()) {
        $accounts[] = $row['id'];
    }
    return $accounts;
}
