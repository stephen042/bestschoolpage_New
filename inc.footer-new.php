<footer class="footer-modern">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <!-- Column 1: Brand -->
                <div>
                    <h5 class="text-xl font-bold text-white mb-4">
                        <?= e($homeContent['site_name'] ?? 'SchoolPro') ?>
                    </h5>
                    <p class="text-sm leading-relaxed">
                        Smart school management solution for modern educational institutions.
                    </p>
                    <div class="flex gap-3 mt-4">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <!-- Column 2: Quick Links -->
                <div>
                    <h5>Quick Links</h5>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?= SITE_URL ?>">Home</a></li>
                        <li><a href="<?= SITE_URL ?>about">About</a></li>
                        <li><a href="<?= SITE_URL ?>services">Services</a></li>
                        <li><a href="<?= SITE_URL ?>contact">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Column 3: Support -->
                <div>
                    <h5>Support</h5>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?= SITE_URL ?>faq">FAQ</a></li>
                        <li><a href="<?= SITE_URL ?>privacy">Privacy Policy</a></li>
                        <li><a href="<?= SITE_URL ?>terms">Terms of Service</a></li>
                        <li><a href="<?= SITE_URL ?>support">Support Center</a></li>
                    </ul>
                </div>
                
                <!-- Column 4: Contact -->
                <div>
                    <h5>Contact Us</h5>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-phone text-indigo-400 mt-1"></i>
                            <span><?= e($homeContent['call_number'] ?? '+1 (555) 123-4567') ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-envelope text-indigo-400 mt-1"></i>
                            <span><?= e($homeContent['email'] ?? 'info@schoolpro.com') ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt text-indigo-400 mt-1"></i>
                            <span>123 Education St, City, State 12345</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-slate-800 pt-6 flex flex-col md:flex-row justify-between items-center text-sm">
                <p>&copy; <?= date('Y') ?> <?= e($homeContent['site_name'] ?? 'SchoolPro') ?>. All rights reserved.</p>
                <p class="mt-2 md:mt-0">Built with <i class="fas fa-heart text-indigo-400"></i> for education</p>
            </div>
        </div>
    </div>
</footer>