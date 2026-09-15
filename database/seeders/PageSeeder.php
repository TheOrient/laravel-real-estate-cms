<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use App\Models\PageDescription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Varsayılan dil kontrolü
        $defaultLanguage = Language::where('is_default', true)->first()
            ?? Language::where('code', config('app.default_language', 'tr'))->first();
        if (! $defaultLanguage) {
            $this->command->error('Default language not found. Run LanguageSeeder first.');

            return;
        }
        $defaultLanguageId = $defaultLanguage->id;
        $englishLanguage = Language::where('code', 'en')->where('is_active', true)->first();
        $demoNoticeTr = '<p><strong>Demo içerik:</strong> Bu sayfadaki kurum, ekip ve iletişim bilgileri tamamen kurgusaldır.</p>';
        $demoNoticeEn = '<p><strong>Demo content:</strong> The agency, team, and contact details on this page are entirely fictional.</p>';

        $corporatePages = [
            [
                'title' => 'Hakkımızda',
                'slug' => 'hakkimizda',
                'show_header' => true,
                'show_footer' => true,
                'description' => 'Real Estate CMS Demo hakkında detaylı bilgi edinin',
                'content' => '<div class="prose-content">
                    <h2>Real Estate CMS Demo Hakkında</h2>
                    <p>Real Estate CMS Demo, Kuşadası ve çevresinde seçilmiş portföy, yerel piyasa bilgisi ve doğrudan iletişimle hizmet veren bir emlak ofisidir. Gayrimenkul alım, satım ve kiralama süreçlerini <strong>şeffaf, düzenli ve güvenli</strong> biçimde yönetmeyi amaçlıyoruz.</p>

                    <h3>Misyonumuz</h3>
                    <p>Kuşadası ve çevresindeki en güncel, doğru ve avantajlı gayrimenkul portföyünü tek bir çatı altında sunarak; alıcı ve satıcıları karşılıklı güven esasına dayalı bir platformda buluşturmak, müşterilerimize <strong>profesyonel ve sonuç odaklı</strong> danışmanlık hizmeti sağlamaktır.</p>

                    <h3>Vizyonumuz</h3>
                    <p>Yerel bilgiyi güncel dijital sunum araçlarıyla birleştiren, müşterilerinin uzun vadeli güvenini önemseyen bir <strong>gayrimenkul ofisi</strong> olmaktır.</p>

                    <h3>Değerlerimiz</h3>
                    <ul>
                        <li><strong>Güvenilirlik:</strong> Tüm portföyümüzün güncelliği, doğruluğu ve yasal uygunluğu için azami özen gösteririz.</li>
                        <li><strong>Şeffaflık:</strong> Yatırım süreçlerinin her aşamasında müşterilerimizi açık ve net bir şekilde bilgilendiririz.</li>
                        <li><strong>Bölgesel Uzmanlık:</strong> Kuşadası pazarının dinamiklerine tam hakimiyetimizle en doğru fiyat analizi ve yatırım danışmanlığını sunarız.</li>
                        <li><strong>Müşteri Odaklılık:</strong> Sadece bir gayrimenkul değil, yaşam boyu güvenebileceğiniz bir yol arkadaşlığı sunmayı hedefleriz.</li>
                    </ul>
                </div>',
                'sort_order' => 1,
                'is_active' => true,
                'en_title' => 'About Us',
                'en_slug' => 'about-us',
                'en_description' => 'Learn more about Real Estate CMS Demo',
                'en_content' => '<div class="prose-content">
                    <h2>About Real Estate CMS Demo</h2>
                    <p>Real Estate CMS Demo is a local real estate office serving Kuşadası and the surrounding area with a curated portfolio, local market knowledge and direct communication. We aim to manage buying, selling and renting processes in a <strong>clear, organised and secure</strong> way.</p>

                    <h3>Our Mission</h3>
                    <p>To bring together the most up-to-date, accurate, and advantageous real estate portfolio in Kuşadası and its surroundings under one roof; connecting buyers and sellers based on mutual trust, and providing our clients with <strong>professional and result-oriented</strong> consultancy.</p>

                    <h3>Our Vision</h3>
                    <p>To be a <strong>local real estate office</strong> that combines regional knowledge with modern presentation tools and values long-term client trust.</p>

                    <h3>Our Values</h3>
                    <ul>
                        <li><strong>Reliability:</strong> We take utmost care for the legal compliance, accuracy, and currency of all our listings.</li>
                        <li><strong>Transparency:</strong> We inform our clients clearly and openly at every stage of the investment process.</li>
                        <li><strong>Regional Expertise:</strong> With our full command over Kuşadası market dynamics, we offer the most accurate price analysis and investment advice.</li>
                        <li><strong>Customer Oriented:</strong> We aim to offer not just a property transaction, but a lifelong companionship you can always rely on.</li>
                    </ul>
                </div>',
            ],
            [
                'title' => 'İletişim',
                'slug' => 'iletisim',
                'show_header' => true,
                'show_footer' => true,
                'description' => 'Kuşadası Real Estate CMS Demo ile iletişime geçin',
                'content' => '<div class="prose-content">
                    <h2>İletişim Bilgileri</h2>
                    <p>Kuşadası ve çevresindeki gayrimenkul ihtiyaçlarınız, yatırım talepleriniz ve portföyümüz hakkında detaylı bilgi almak için bizimle dilediğiniz zaman iletişime geçebilirsiniz.</p>

                    <div class="contact-info">
                        <h3>Real Estate CMS Demo</h3>
                        <ul>
                            <li><strong>Hizmet bölgesi:</strong> Kuşadası / Aydın</li>
                            <li><strong>Email:</strong> demo@example.test</li>
                            <li><strong>Görüşme:</strong> Randevulu ofis ve yerinde portföy görüşmesi</li>
                        </ul>
                    </div>

                    <p>Ofisimizde sizi ağırlamaktan ve bir kahve eşliğinde hayalinizdeki gayrimenkulü konuşmaktan memnuniyet duyarız.</p>
                </div>',
                'sort_order' => 2,
                'is_active' => true,
                'en_title' => 'Contact',
                'en_slug' => 'contact',
                'en_description' => 'Get in touch with Real Estate CMS Demo Kuşadası',
                'en_content' => '<div class="prose-content">
                    <h2>Contact Information</h2>
                    <p>You can contact us anytime for your real estate needs, investment requests, and detailed information about our portfolio in Kuşadası and surrounding areas.</p>

                    <div class="contact-info">
                        <h3>Real Estate CMS Demo</h3>
                        <ul>
                            <li><strong>Service area:</strong> Kuşadası / Aydın / Turkey</li>
                            <li><strong>Email:</strong> demo@example.test</li>
                            <li><strong>Meetings:</strong> By appointment at the office or property</li>
                        </ul>
                    </div>

                    <p>We would be delighted to welcome you to our office and discuss your dream property over a cup of coffee.</p>
                </div>',
            ],
            [
                'title' => 'Kariyer',
                'slug' => 'kariyer',
                'description' => 'Real Estate CMS Demo bünyesinde gayrimenkul danışmanlığı kariyeri',
                'content' => '<div class="prose-content">
                    <h2>Kariyer Fırsatları</h2>
                    <p>Gayrimenkul sektörüne ilgi duyuyor, Kuşadası bölgesinde yüksek kazançlı ve dinamik bir kariyer hedefliyorsanız, Real Estate CMS Demo ailesinin bir parçası olabilirsiniz.</p>

                    <h3>Neden Real Estate CMS Demo?</h3>
                    <ul>
                        <li>Kuşadası pazarında güçlü ve tanınan marka imajı</li>
                        <li>Geniş ve sürekli güncellenen hazır portföy desteği</li>
                        <li>Profesyonel saha ve pazarlama destekleri</li>
                        <li>Yüksek prim oranları ve adil kazanç modeli</li>
                    </ul>

                    <h3>Açık Pozisyonlar</h3>
                    <p><strong>Gayrimenkul Danışmanı (Kuşadası Ofisi):</strong> İletişim becerileri yüksek, saha çalışmasına yatkın ve kendi işinin sahibi olmak isteyen çalışma arkadaşları arıyoruz. Özgeçmişinizi <a href="mailto:demo@example.test">demo@example.test</a> adresine gönderebilirsiniz.</p>
                </div>',
                'sort_order' => 3,
                'is_active' => true,
                'en_title' => 'Careers',
                'en_slug' => 'careers',
                'en_description' => 'Real estate consultancy career opportunities at Real Estate CMS Demo',
                'en_content' => '<div class="prose-content">
                    <h2>Career Opportunities</h2>
                    <p>If you are interested in the real estate sector and aiming for a high-income, dynamic career in the Kuşadası region, you can become a part of the Real Estate CMS Demo family.</p>

                    <h3>Why Real Estate CMS Demo?</h3>
                    <ul>
                        <li>Strong and well-known brand image in the Kuşadası market</li>
                        <li>Extensive and constantly updated ready-made portfolio support</li>
                        <li>Professional field and marketing supports</li>
                        <li>High commission rates and a fair income model</li>
                    </ul>

                    <h3>Open Positions</h3>
                    <p><strong>Real Estate Consultant (Kuşadası Office):</strong> We are looking for team members with strong communication skills, prone to field work, and willing to manage their own business. You can send your resume to <a href="mailto:demo@example.test">demo@example.test</a>.</p>
                </div>',
            ],
        ];

        $helpPages = [
            [
                'title' => 'Yardım Merkezi',
                'slug' => 'yardim-merkezi',
                'description' => 'Sık sorulan sorular ve gayrimenkul süreçleri rehberi',
                'content' => '<div class="prose-content">
                    <h2>Yardım Merkezi & Sık Sorulan Sorular</h2>
                    <p>Kuşadası\'nda gayrimenkul alırken, satarken veya kiralarken merak ettiğiniz temel konuları sizler için derledik.</p>

                    <h3>Sık Sorulan Sorular</h3>

                    <h4>Gayrimenkulümü satmak / kiralamak istiyorum, süreç nasıl işliyor?</h4>
                    <p>Bizimle iletişime geçtiğinizde uzman danışmanlarımız mülkünüzü yerinde ziyaret eder, doğru bölge analizleriyle ekspertiz değerini belirler ve profesyonel fotoğraf/video çekimleriyle hızlıca pazarlamaya başlar.</p>

                    <h4>Kuşadası\'nda yabancı uyrukluların mülk edinme süreci nasıldır?</h4>
                    <p>Yabancı alıcıların güncel tapu ve belge süreçleri taşınmaza ve uyruğa göre değişebilir. Resmî gereklilikler işlem öncesinde Tapu ve Kadastro Genel Müdürlüğü kaynaklarından doğrulanmalıdır.</p>

                    <h4>Tapu işlemlerinde hangi belgeler gereklidir?</h4>
                    <p>Kimlik belgesi, tapu aslı veya fotokopisi, belediyeden alınacak rayiç bedel belgesi ve zorunlu deprem sigortası (DASK) poliçesi temel belgeler arasındadır. Sürecin takibini ekibimiz yapmaktadır.</p>
                </div>',
                'sort_order' => 11,
                'is_active' => true,
                'en_title' => 'Help Center',
                'en_slug' => 'help-center',
                'en_description' => 'Frequently asked questions and real estate process guide',
                'en_content' => '<div class="prose-content">
                    <h2>Help Center & FAQ</h2>
                    <p>We have compiled the essential topics for you regarding buying, selling, or renting real estate in Kuşadası.</p>

                    <h3>Frequently Asked Questions</h3>

                    <h4>I want to sell / rent my property, how does the process work?</h4>
                    <p>When you contact us, our expert consultants visit your property on-site, determine its valuation value with accurate regional analysis, and start marketing it quickly with professional photo/video shoots.</p>

                    <h4>What is the property acquisition process for foreigners in Kuşadası?</h4>
                    <p>The military zone checks and title deed processes of our foreign guests are carried out safely and completely under the legal and bureaucratic guidance of our office.</p>

                    <h4>Which documents are required for title deed transactions?</h4>
                    <p>ID document, original or photocopy of the title deed, current market value document from the municipality, and compulsory earthquake insurance (DASK) policy are among the basic documents. Our team tracks the entire process.</p>
                </div>',
            ],
            [
                'title' => 'Güvenli Gayrimenkul İşlemleri',
                'slug' => 'guvenli-gayrimenkul-islemleri',
                'description' => 'Emlak alım-satımında dikkat edilmesi gereken güvenlik adımları',
                'content' => '<div class="prose-content">
                    <h2>Güvenli Gayrimenkul İşlemleri İçin Öneriler</h2>
                    <p>Yatırımlarınızın ve birikimlerinizin güvence altında olması için gayrimenkul süreçlerinde hukuki ve resmî adımlara dikkat edilmesi hayati önem taşır.</p>

                    <h3>Önemli Tavsiyeler</h3>
                    <ul>
                        <li><strong>Tapu Kaydını Sorgulayın:</strong> Satın alacağınız mülkün üzerinde ipotek, haciz veya şerh olup olmadığını mutlaka resmî kanallardan doğrulayın. Real Estate CMS Demo olarak bu kontrolleri sizin adınıza yapıyoruz.</li>
                        <li><strong>Ödemeleri Güvenceye Alın:</strong> Resmî tapu devri gerçekleşmeden elden veya hesaba kontrolsüz kapora/bedel transferi yapmayın. Blokeli çek veya Tapu Takas sistemlerini tercih edin.</li>
                        <li><strong>Sözleşmesiz İşlem Yapmayın:</strong> Alım-satım kararlarınızı mutlaka tarafların haklarını koruyan ıslak imzalı bir Taşınmaz Gösterme ve Satış Vaadi Sözleşmesi ile resmiyete dökün.</li>
                    </ul>
                </div>',
                'sort_order' => 12,
                'is_active' => true,
                'en_title' => 'Safe Transactions',
                'en_slug' => 'safe-transactions',
                'en_description' => 'Security steps to consider in real estate trading',
                'en_content' => '<div class="prose-content">
                    <h2>Recommendations for Safe Real Estate Transactions</h2>
                    <p>In order to protect your investments and savings, it is vital to pay attention to legal and official steps in real estate processes.</p>

                    <h3>Key Recommendations</h3>
                    <ul>
                        <li><strong>Check Title Deed Records:</strong> Always verify through official channels whether there are any mortgages, liens, or annotations on the property you will purchase. As Real Estate CMS Demo, we conduct these checks on your behalf.</li>
                        <li><strong>Secure Your Payments:</strong> Do not make uncontrolled deposit transfers before the official title deed transfer takes place. Prefer blocked checks or official Title Deed Swap systems.</li>
                        <li><strong>Do Not Transact Without Contract:</strong> Always formalize your buying and selling decisions with a wet-signed Real Estate Showing and Sales Promise Agreement that protects the rights of both parties.</li>
                    </ul>
                </div>',
            ],
            [
                'title' => 'Gizlilik Politikası ve KVKK',
                'slug' => 'gizlilik-politikasi',
                'description' => 'Müşteri verilerinin korunması ve gizlilik politikası',
                'content' => '<div class="prose-content">
                    <h2>Gizlilik Politikası ve KVKK Aydınlatma Metni</h2>
                    <p>Real Estate CMS Demo olarak, müşterilerimizin ve web sitemizi ziyaret eden kullanıcılarımızın kişisel verilerinin korunmasına büyük önem veriyoruz.</p>

                    <h3>Verilerin İşlenmesi ve Amacı</h3>
                    <p>Tarafımıza iletişim formları, telefon veya fiziki ortamda sağladığınız ad, soyad, telefon ve e-posta gibi kişisel verileriniz; yalnızca taleplerinize dönüş yapmak, aradığınız kriterlere uygun gayrimenkul portföyünü sunmak ve yasal yükümlülükleri yerine getirmek amacıyla 6698 sayılı KVKK kapsamında işlenmektedir.</p>
                    <p>Verileriniz, açık rızanız olmaksızın veya yasal zorunluluklar dışında asla üçüncü şahıslarla paylaşılmaz ve güvenli sunucularda saklanır.</p>
                </div>',
                'sort_order' => 15,
                'is_active' => true,
                'en_title' => 'Privacy Policy',
                'en_slug' => 'privacy-policy',
                'en_description' => 'Protection of customer data and privacy policy',
                'en_content' => '<div class="prose-content">
                    <h2>Privacy Policy & GDPR</h2>
                    <p>As Real Estate CMS Demo, we attach great importance to the protection of the personal data of our customers and visitors.</p>

                    <h3>Processing and Purpose of Data</h3>
                    <p>Your personal data such as name, surname, phone number, and e-mail provided to us through contact forms, telephone, or physical environment are processed solely to respond to your requests, present property portfolios matching your criteria, and fulfill legal obligations.</p>
                    <p>Your data is never shared with third parties without your explicit consent or unless required by law, and is stored securely.</p>
                </div>',
            ],
        ];

        // The public site belongs to a small local agency. Recruitment and
        // marketplace-style help pages are intentionally not published.
        $corporatePages = array_values(array_filter(
            $corporatePages,
            fn (array $page) => ! in_array($page['slug'], ['kariyer', 'basin'], true)
        ));
        $helpPages = array_values(array_filter(
            $helpPages,
            fn (array $page) => $page['slug'] !== 'yardim-merkezi'
        ));

        // Seeder Gruplarını Çalıştır
        $seedGroups = [
            $corporatePages,
            $helpPages,
        ];

        foreach ($seedGroups as $pages) {
            foreach ($pages as $pageData) {
                // Ana kayıt (dil bağımsız)
                $page = Page::updateOrCreate(
                    ['title' => $pageData['title']],
                    [
                        'is_active' => $pageData['is_active'] ?? true,
                        'sort_order' => $pageData['sort_order'] ?? 0,
                        'show_header' => $pageData['show_header'] ?? true,
                        'show_footer' => $pageData['show_footer'] ?? true,
                    ]
                );

                PageDescription::updateOrCreate(
                    [
                        'page_id' => $page->id,
                        'language_id' => $defaultLanguageId,
                    ],
                    [
                        'slug' => $pageData['slug'] ?? Str::slug($pageData['title']),
                        'meta_description' => $pageData['description'] ?? null,
                        'content' => $demoNoticeTr.($pageData['content'] ?? ''),
                        'title' => $pageData['title'] ?? null,
                    ]
                );

                if ($englishLanguage && isset($pageData['en_title'], $pageData['en_slug'])) {
                    PageDescription::updateOrCreate(
                        [
                            'page_id' => $page->id,
                            'language_id' => $englishLanguage->id,
                        ],
                        [
                            'slug' => $pageData['en_slug'],
                            'meta_description' => $pageData['en_description'] ?? null,
                            'content' => $demoNoticeEn.($pageData['en_content'] ?? ''),
                            'title' => $pageData['en_title'],
                        ]
                    );
                }
            }
        }
    }
}
