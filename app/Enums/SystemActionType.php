<?php
namespace App\Enums;

enum SystemActionType: string
{
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case CREATE_ACCOUNT = 'create_new_acc';
    case START_SESSION = 'start_session';
    case EDIT_SESSION_TIME = 'edit_session_time';
    case SESSION_DISCOUNT = 'session_discount';
    case ADD_SESSION_PUNISHMENT = 'add_session_punchment';
    case SPLIT_SESSION = 'split_session';
    case SESSION_CHECKOUT = 'session_checkout';
    case DELETE_SESSION = 'delete_session';
    case SALE_PROCESS = 'sale_process';
    case ADD_EXPENSE = 'add_expense';
    case ADD_EXPENSE_DRAFT = 'add_expense_draft';
    case ADD_SUBSCRIPTION = 'add_subscription';
    case DECREASE_REMAINING_VISITS = 'decrease_remaining_visits';
    case RENEW_SUBSCRIPTION = 'renew_subscription';
    case ADD_BOOKINGS = 'add_bookings';
    case ADD_DEPOSIT = 'add_deposit';
    case START_BOOKING = 'start_booking';
    case ADD_BOOKING_PUNISHMENT = 'add_booking_punchment';
    case BOOKING_CHECKOUT = 'booking_checkout';
    case EDIT_BOOKING_DETAILS = 'edit_booking_details';
    case ADD_NEW_CLIENT = 'add_new_client';
    case ADD_NEW_PRODUCT = 'add_new_product';
    case ADD_QTY_OLD_PRODUCT = 'add_qty_old_product';
    case EDIT_PRODUCT = 'edit_product';
    case ADD_NEW_HALL = 'add_new_hall';
    case EDIT_HALL = 'edit_hall';
    case DELETE_HALL = 'delete_hall';
    case ADD_BASE_HOUR_RECORD = 'add_base_hour_record';
    case ADD_IMPORTANT_PRODUCT = 'add_important_product';
    case DELETE_IMPORTANT_PRODUCT = 'delete_important_product';
    case ADD_NEW_EXPENSE_TYPE = 'add_new_expense_type';
    case ADD_FULL_DAY_HOURS = 'add_full_day_hours_record';
    case ADD_SUBSCRIPTION_PLAN = 'add_subscriptions_plan';
    case EDIT_SUBSCRIPTION_PLAN = 'edit_subscriptions_plan';
    case START_SHIFT = 'start_shift';
    case END_SHIFT = 'end_shift';
    case READ_DAILY_SHIFTS = 'read_daily_shifts';
}
