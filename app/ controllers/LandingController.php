<?php

class LandingController {
    public function index(): void {
        if (current_user()) {
            redirect_to(app_url());
        }

        $programs  = ProgramModel::all();
        $donations = DonationModel::all();


        $active = array_filter($programs, fn($p) => $p['status'] === 'active');
        usort($active, fn($a, $b) => $b['collected'] <=> $a['collected']);
        $displayPrograms = array_values($active);

        // Total donasi terverifikasi dihitung dari transaksi verified (real-time & akurat)
        $totalCollected = DonationModel::totalCollectedRp();
        $totalTarget    = array_sum(array_column($programs, 'target'));
        $totalProgram   = count($active);
        $donaturUnik    = DonationModel::uniqueDonors();
        $verifiedCount  = count(DonationModel::verified());


        $topDonors = DonationModel::topDonors(5);

        View::render('landing/index', compact(
            'displayPrograms',
            'totalCollected',
            'totalTarget',
            'totalProgram',
            'donaturUnik',
            'verifiedCount',
            'topDonors'
        ));
    }
}
