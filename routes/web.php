<?php


Router::get('',           fn() => (new LandingController)->index());
Router::get('landing',    fn() => (new LandingController)->index());
Router::get('auth/login', fn() => (new AuthController)->showLogin());


Router::get('app',        fn() => (new AppController)->show());


Router::post('auth/login',    fn() => (new AuthController)->login());
Router::post('auth/register', fn() => (new AuthController)->register());
Router::post('auth/logout',   fn() => (new AuthController)->logout());


Router::post('donation/donate', fn() => (new DonationController)->donate());
Router::post('donation/verify', fn() => (new DonationController)->verify());
Router::post('donation/reject', fn() => (new DonationController)->verify());


Router::post('program/add',    fn() => (new ProgramController)->add());
Router::post('program/edit',   fn() => (new ProgramController)->edit());
Router::post('program/close',  fn() => (new ProgramController)->close());
Router::post('program/reopen', fn() => (new ProgramController)->reopen());
Router::post('program/delete', fn() => (new ProgramController)->delete());


Router::post('staff/add',          fn() => (new StaffController)->add());
Router::post('staff/set-status',   fn() => (new StaffController)->setStatus());
Router::post('staff/delete',       fn() => (new StaffController)->delete());
Router::post('staff/add-rekan',    fn() => (new StaffController)->addRekan());
Router::post('staff/remove-rekan', fn() => (new StaffController)->removeRekan());


Router::post('profile/update', fn() => (new ProfileController)->update());


Router::post('settings/update', fn() => (new SettingsController)->update());
