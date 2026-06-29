<?php
if (!session()->get('logged_in')) {
    return redirect()->to('/member/login');
}