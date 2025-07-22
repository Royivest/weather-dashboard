<?php
/**
 * Session handler
 * This file contains all functions related to user sessions and authentication
 * Provides user login status check, access control, etc.
 */

// Start session
session_start();

/**
 * Check if user is logged in
 * Determine by checking if user ID exists in session
 * @return bool Returns true if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['customerID']) || isset($_SESSION['staffID']);
}

/**
 * Check if user is staff
 * @return bool Returns true if user is staff, false otherwise
 */
function is_staff() {
    return isset($_SESSION['userType']) && $_SESSION['userType'] === 'staff';
}

/**
 * Check if user is customer
 * @return bool Returns true if user is customer, false otherwise
 */
function is_customer() {
    return isset($_SESSION['userType']) && $_SESSION['userType'] === 'customer';
}

/**
 * Require user login
 * If user is not logged in, redirect to the corresponding login page
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: " . (is_staff() ? "../staff/login.php" : "../customer/index.php") . "?error=session");
        exit();
    }
}

/**
 * Require staff permission
 * If user is not staff, redirect to customer page
 */
function require_staff() {
    if (!is_staff()) {
        header("Location: ../customer/index.php?error=unauthorized");
        exit();
    }
}

/**
 * Require customer permission
 * If user is not customer, redirect to staff login page
 */
function require_customer() {
    if (!is_customer()) {
        header("Location: ../staff/login.php?error=unauthorized");
        exit();
    }
}

/**
 * Handle 'remember me' feature
 * Check and verify remember me token, auto-login user if valid
 */
function check_remember_token() {
    if (!is_logged_in() && isset($_COOKIE['remember_token'])) {
        require_once('db_connect.php');
        
        // 獲取並驗證令牌
        $token = $_COOKIE['remember_token'];
        $stmt = $conn->prepare("SELECT user_id, user_type FROM remember_tokens WHERE token = ? AND expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $token_data = $result->fetch_assoc();
            
            // 根據用戶類型獲取用戶信息
            if ($token_data['user_type'] === 'customer') {
                $stmt = $conn->prepare("SELECT cid, cname FROM customer WHERE cid = ?");
            } else {
                $stmt = $conn->prepare("SELECT sid, sname FROM staff WHERE sid = ?");
            }
            
            $stmt->bind_param("i", $token_data['user_id']);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            // 如果找到用戶，設置會話信息
            if ($user) {
                $_SESSION['userType'] = $token_data['user_type'];
                if ($token_data['user_type'] === 'customer') {
                    $_SESSION['customerID'] = $user['cid'];
                    $_SESSION['username'] = $user['cname'];
                } else {
                    $_SESSION['staffID'] = $user['sid'];
                    $_SESSION['username'] = $user['sname'];
                }
            }
        }
    }
}

// 在每個頁面加載時檢查記住我令牌
check_remember_token();
?> 