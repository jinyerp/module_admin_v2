<?php
// 이 파일의 시더를 실행하려면 아래 명령어를 사용하세요:
// php artisan db:seed --class=Jiny\\Admin\\Database\\Seeders\\AdminCountrySeeder
//
// 데이터베이스를 초기화하려면 --force 옵션을 추가할 수 있습니다.
// 예시: php artisan db:seed --class=Jiny\\Admin\\Database\\Seeders\\AdminCountrySeeder --force

namespace Jiny\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminCountrySeeder extends Seeder
{
    public function run()
    {
        $countries = $this->countries();
        foreach ($countries as $code => $country) {
            DB::table('admin_country')->updateOrInsert([
                'code' => $country['code'] ?? $code,
            ], [
                'name' => $country['ko'] ?? $country['en'] ?? $code,
                'flag' => strtolower($country['code'] ?? $code),
                'enable' => 0, // 항상 비활성화로 저장
                'description' => $country['en'] ?? '',
                'continent' => $country['continent'] ?? null,
                'continent_manager' => $country['continent_manager'] ?? null,
                'continent_manager_email' => $country['continent_manager_email'] ?? null,
                'latitude' => $country['latitude'] ?? null,
                'longitude' => $country['longitude'] ?? null,
            ]);
        }
    }

    private function countries()
    {
        return array_merge(
            $this->asiaCountries(),
            $this->europeCountries(),
            $this->africaCountries(),
            $this->northAmericaCountries(),
            $this->southAmericaCountries(),
            $this->oceaniaCountries(),
            $this->otherTerritories()
        );
    }

    // 이하 각 대륙별 ISO 3166-1 전체 국가 목록을 반환하는 메소드 구현
    // 예시: 아시아
    private function asiaCountries()
    {
        $manager = '아시아총괄';
        $manager_email = 'asia_manager@example.com';
        $continent = 'Asia';
        return [
            'AF'=>["code"=>"AF","en"=>"Afghanistan","ko"=>"아프가니스탄","ja"=>"アフガニスタン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'AM'=>["code"=>"AM","en"=>"Armenia","ko"=>"아르메니아","ja"=>"アルメニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'AZ'=>["code"=>"AZ","en"=>"Azerbaijan","ko"=>"아제르바이잔","ja"=>"アゼルバイジャン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BH'=>["code"=>"BH","en"=>"Bahrain","ko"=>"바레인","ja"=>"バーレーン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BD'=>["code"=>"BD","en"=>"Bangladesh","ko"=>"방글라데시","ja"=>"バングラデシュ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BT'=>["code"=>"BT","en"=>"Bhutan","ko"=>"부탄","ja"=>"ブータン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BN'=>["code"=>"BN","en"=>"Brunei","ko"=>"브루나이","ja"=>"ブルネイ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KH'=>["code"=>"KH","en"=>"Cambodia","ko"=>"캄보디아","ja"=>"カンボジア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CN'=>["code"=>"CN","en"=>"China","ko"=>"중국","ja"=>"中国","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CY'=>["code"=>"CY","en"=>"Cyprus","ko"=>"키프로스","ja"=>"キプロス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GE'=>["code"=>"GE","en"=>"Georgia","ko"=>"조지아","ja"=>"ジョージア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'HK'=>["code"=>"HK","en"=>"Hong Kong","ko"=>"홍콩","ja"=>"香港","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'IN'=>["code"=>"IN","en"=>"India","ko"=>"인도","ja"=>"インド","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ID'=>["code"=>"ID","en"=>"Indonesia","ko"=>"인도네시아","ja"=>"インドネシア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'IR'=>["code"=>"IR","en"=>"Iran","ko"=>"이란","ja"=>"イラン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'IQ'=>["code"=>"IQ","en"=>"Iraq","ko"=>"이라크","ja"=>"イラク","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'IL'=>["code"=>"IL","en"=>"Israel","ko"=>"이스라엘","ja"=>"イスラエル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'JP'=>["code"=>"JP","en"=>"Japan","ko"=>"일본","ja"=>"日本","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email,"latitude"=>"35.6895","longitude"=>"139.6917"],
            'JO'=>["code"=>"JO","en"=>"Jordan","ko"=>"요르단","ja"=>"ヨルダン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KZ'=>["code"=>"KZ","en"=>"Kazakhstan","ko"=>"카자흐스탄","ja"=>"カザフスタン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KP'=>["code"=>"KP","en"=>"North Korea","ko"=>"북한","ja"=>"北朝鮮","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KR'=>["code"=>"KR","en"=>"South Korea","ko"=>"대한민국","ja"=>"韓国","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email,"latitude"=>"36.5","longitude"=>"127.8"],
            'KW'=>["code"=>"KW","en"=>"Kuwait","ko"=>"쿠웨이트","ja"=>"クウェート","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KG'=>["code"=>"KG","en"=>"Kyrgyzstan","ko"=>"키르기스스탄","ja"=>"キルギス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LA'=>["code"=>"LA","en"=>"Laos","ko"=>"라오스","ja"=>"ラオス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LB'=>["code"=>"LB","en"=>"Lebanon","ko"=>"레바논","ja"=>"レバノン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MO'=>["code"=>"MO","en"=>"Macau","ko"=>"마카오","ja"=>"マカオ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MY'=>["code"=>"MY","en"=>"Malaysia","ko"=>"말레이시아","ja"=>"マレーシア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MV'=>["code"=>"MV","en"=>"Maldives","ko"=>"몰디브","ja"=>"モルディブ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MN'=>["code"=>"MN","en"=>"Mongolia","ko"=>"몽골","ja"=>"モンゴル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MM'=>["code"=>"MM","en"=>"Myanmar","ko"=>"미얀마","ja"=>"ミャンマー","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NP'=>["code"=>"NP","en"=>"Nepal","ko"=>"네팔","ja"=>"ネパール","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'OM'=>["code"=>"OM","en"=>"Oman","ko"=>"오만","ja"=>"オマーン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PK'=>["code"=>"PK","en"=>"Pakistan","ko"=>"파키스탄","ja"=>"パキスタン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PS'=>["code"=>"PS","en"=>"Palestine","ko"=>"팔레스타인","ja"=>"パレスチナ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PH'=>["code"=>"PH","en"=>"Philippines","ko"=>"필리핀","ja"=>"フィリピン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'QA'=>["code"=>"QA","en"=>"Qatar","ko"=>"카타르","ja"=>"カタール","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SA'=>["code"=>"SA","en"=>"Saudi Arabia","ko"=>"사우디아라비아","ja"=>"サウジアラビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SG'=>["code"=>"SG","en"=>"Singapore","ko"=>"싱가포르","ja"=>"シンガポール","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LK'=>["code"=>"LK","en"=>"Sri Lanka","ko"=>"스리랑카","ja"=>"スリランカ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SY'=>["code"=>"SY","en"=>"Syria","ko"=>"시리아","ja"=>"シリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TW'=>["code"=>"TW","en"=>"Taiwan","ko"=>"대만","ja"=>"台湾","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TJ'=>["code"=>"TJ","en"=>"Tajikistan","ko"=>"타지키스탄","ja"=>"タジキスタン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TH'=>["code"=>"TH","en"=>"Thailand","ko"=>"태국","ja"=>"タイ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TL'=>["code"=>"TL","en"=>"Timor-Leste","ko"=>"동티모르","ja"=>"東ティモール","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TR'=>["code"=>"TR","en"=>"Turkey","ko"=>"터키","ja"=>"トルコ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TM'=>["code"=>"TM","en"=>"Turkmenistan","ko"=>"투르크메니스탄","ja"=>"トルクメニスタン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'AE'=>["code"=>"AE","en"=>"United Arab Emirates","ko"=>"아랍에미리트","ja"=>"アラブ首長国連邦","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'UZ'=>["code"=>"UZ","en"=>"Uzbekistan","ko"=>"우즈베키스탄","ja"=>"ウズベキスタン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'VN'=>["code"=>"VN","en"=>"Vietnam","ko"=>"베트남","ja"=>"ベトナム","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'YE'=>["code"=>"YE","en"=>"Yemen","ko"=>"예멘","ja"=>"イエメン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
        ];
    }

    private function europeCountries()
    {
        $manager = '유럽총괄';
        $manager_email = 'europe_manager@example.com';
        $continent = 'Europe';
        return [
            'AL'=>["code"=>"AL","en"=>"Albania","ko"=>"알바니아","ja"=>"アルバニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'AD'=>["code"=>"AD","en"=>"Andorra","ko"=>"안도라","ja"=>"アンドラ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'AT'=>["code"=>"AT","en"=>"Austria","ko"=>"오스트리아","ja"=>"オーストリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BY'=>["code"=>"BY","en"=>"Belarus","ko"=>"벨라루스","ja"=>"ベラルーシ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BE'=>["code"=>"BE","en"=>"Belgium","ko"=>"벨기에","ja"=>"ベルギー","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BA'=>["code"=>"BA","en"=>"Bosnia and Herzegovina","ko"=>"보스니아 헤르체고비나","ja"=>"ボスニア・ヘルツェゴビナ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BG'=>["code"=>"BG","en"=>"Bulgaria","ko"=>"불가리아","ja"=>"ブルガリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'HR'=>["code"=>"HR","en"=>"Croatia","ko"=>"크로아티아","ja"=>"クロアチア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CY'=>["code"=>"CY","en"=>"Cyprus","ko"=>"키프로스","ja"=>"キプロス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CZ'=>["code"=>"CZ","en"=>"Czechia","ko"=>"체코","ja"=>"チェコ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'DK'=>["code"=>"DK","en"=>"Denmark","ko"=>"덴마크","ja"=>"デンマーク","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'EE'=>["code"=>"EE","en"=>"Estonia","ko"=>"에스토니아","ja"=>"エストニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'FI'=>["code"=>"FI","en"=>"Finland","ko"=>"핀란드","ja"=>"フィンランド","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'FR'=>["code"=>"FR","en"=>"France","ko"=>"프랑스","ja"=>"フランス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email,"latitude"=>"48.8566","longitude"=>"2.3522"],
            'GE'=>["code"=>"GE","en"=>"Georgia","ko"=>"조지아","ja"=>"ジョージア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'DE'=>["code"=>"DE","en"=>"Germany","ko"=>"독일","ja"=>"ドイツ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GR'=>["code"=>"GR","en"=>"Greece","ko"=>"그리스","ja"=>"ギリシャ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'HU'=>["code"=>"HU","en"=>"Hungary","ko"=>"헝가리","ja"=>"ハンガリー","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'IS'=>["code"=>"IS","en"=>"Iceland","ko"=>"아이슬란드","ja"=>"アイスランド","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'IE'=>["code"=>"IE","en"=>"Ireland","ko"=>"아일랜드","ja"=>"アイルランド","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'IT'=>["code"=>"IT","en"=>"Italy","ko"=>"이탈리아","ja"=>"イタリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LV'=>["code"=>"LV","en"=>"Latvia","ko"=>"라트비아","ja"=>"ラトビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LI'=>["code"=>"LI","en"=>"Liechtenstein","ko"=>"리히텐슈타인","ja"=>"リヒテンシュタイン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LT'=>["code"=>"LT","en"=>"Lithuania","ko"=>"리투아니아","ja"=>"リトアニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LU'=>["code"=>"LU","en"=>"Luxembourg","ko"=>"룩셈부르크","ja"=>"ルクセンブルク","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MT'=>["code"=>"MT","en"=>"Malta","ko"=>"몰타","ja"=>"マルタ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MD'=>["code"=>"MD","en"=>"Moldova","ko"=>"몰도바","ja"=>"モルドバ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MC'=>["code"=>"MC","en"=>"Monaco","ko"=>"모나코","ja"=>"モナコ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ME'=>["code"=>"ME","en"=>"Montenegro","ko"=>"몬테네그로","ja"=>"モンテネグロ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NL'=>["code"=>"NL","en"=>"Netherlands","ko"=>"네덜란드","ja"=>"オランダ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MK'=>["code"=>"MK","en"=>"North Macedonia","ko"=>"북마케도니아","ja"=>"北マケドニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NO'=>["code"=>"NO","en"=>"Norway","ko"=>"노르웨이","ja"=>"ノルウェー","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PL'=>["code"=>"PL","en"=>"Poland","ko"=>"폴란드","ja"=>"ポーランド","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PT'=>["code"=>"PT","en"=>"Portugal","ko"=>"포르투갈","ja"=>"ポルトガル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'RO'=>["code"=>"RO","en"=>"Romania","ko"=>"루마니아","ja"=>"ルーマニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'RU'=>["code"=>"RU","en"=>"Russia","ko"=>"러시아","ja"=>"ロシア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SM'=>["code"=>"SM","en"=>"San Marino","ko"=>"산마리노","ja"=>"サンマリノ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'RS'=>["code"=>"RS","en"=>"Serbia","ko"=>"세르비아","ja"=>"セルビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SK'=>["code"=>"SK","en"=>"Slovakia","ko"=>"슬로바키아","ja"=>"スロバキア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SI'=>["code"=>"SI","en"=>"Slovenia","ko"=>"슬로베니아","ja"=>"スロベニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ES'=>["code"=>"ES","en"=>"Spain","ko"=>"스페인","ja"=>"スペイン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SE'=>["code"=>"SE","en"=>"Sweden","ko"=>"스웨덴","ja"=>"スウェーデン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CH'=>["code"=>"CH","en"=>"Switzerland","ko"=>"스위스","ja"=>"スイス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'UA'=>["code"=>"UA","en"=>"Ukraine","ko"=>"우크라이나","ja"=>"ウクライナ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GB'=>["code"=>"GB","en"=>"United Kingdom","ko"=>"영국","ja"=>"イギリス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'VA'=>["code"=>"VA","en"=>"Vatican City","ko"=>"바티칸","ja"=>"バチカン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            // 기타 유럽 국가 및 해외 영토(AX, FO, GG, GI, IM, JE, ME, SJ, etc) 필요시 추가
        ];
    }

    private function africaCountries()
    {
        $manager = '아프리카총괄';
        $manager_email = 'africa_manager@example.com';
        $continent = 'Africa';
        return [
            'DZ'=>["code"=>"DZ","en"=>"Algeria","ko"=>"알제리","ja"=>"アルジェリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'AO'=>["code"=>"AO","en"=>"Angola","ko"=>"앙골라","ja"=>"アンゴラ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BJ'=>["code"=>"BJ","en"=>"Benin","ko"=>"베냉","ja"=>"ベナン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BW'=>["code"=>"BW","en"=>"Botswana","ko"=>"보츠와나","ja"=>"ボツワナ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BF'=>["code"=>"BF","en"=>"Burkina Faso","ko"=>"부르키나파소","ja"=>"ブルキナファソ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BI'=>["code"=>"BI","en"=>"Burundi","ko"=>"부룬디","ja"=>"ブルンジ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CM'=>["code"=>"CM","en"=>"Cameroon","ko"=>"카메룬","ja"=>"カメルーン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CV'=>["code"=>"CV","en"=>"Cape Verde","ko"=>"카보베르데","ja"=>"カーボベルデ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CF'=>["code"=>"CF","en"=>"Central African Republic","ko"=>"중앙아프리카공화국","ja"=>"中央アフリカ共和国","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TD'=>["code"=>"TD","en"=>"Chad","ko"=>"차드","ja"=>"チャド","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KM'=>["code"=>"KM","en"=>"Comoros","ko"=>"코모로","ja"=>"コモロ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CG'=>["code"=>"CG","en"=>"Congo","ko"=>"콩고","ja"=>"コンゴ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CD'=>["code"=>"CD","en"=>"Democratic Republic of the Congo","ko"=>"콩고민주공화국","ja"=>"コンゴ民主共和国","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'DJ'=>["code"=>"DJ","en"=>"Djibouti","ko"=>"지부티","ja"=>"ジブチ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'EG'=>["code"=>"EG","en"=>"Egypt","ko"=>"이집트","ja"=>"エジプト","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GQ'=>["code"=>"GQ","en"=>"Equatorial Guinea","ko"=>"적도기니","ja"=>"赤道ギニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ER'=>["code"=>"ER","en"=>"Eritrea","ko"=>"에리트레아","ja"=>"エリトリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SZ'=>["code"=>"SZ","en"=>"Eswatini","ko"=>"에스와티니","ja"=>"エスワティニ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ET'=>["code"=>"ET","en"=>"Ethiopia","ko"=>"에티오피아","ja"=>"エチオピア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GA'=>["code"=>"GA","en"=>"Gabon","ko"=>"가봉","ja"=>"ガボン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GM'=>["code"=>"GM","en"=>"Gambia","ko"=>"감비아","ja"=>"ガンビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GH'=>["code"=>"GH","en"=>"Ghana","ko"=>"가나","ja"=>"ガーナ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GN'=>["code"=>"GN","en"=>"Guinea","ko"=>"기니","ja"=>"ギニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GW'=>["code"=>"GW","en"=>"Guinea-Bissau","ko"=>"기니비사우","ja"=>"ギニアビサウ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CI'=>["code"=>"CI","en"=>"Ivory Coast","ko"=>"코트디부아르","ja"=>"コートジボワール","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KE'=>["code"=>"KE","en"=>"Kenya","ko"=>"케냐","ja"=>"ケニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LS'=>["code"=>"LS","en"=>"Lesotho","ko"=>"레소토","ja"=>"レソト","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LR'=>["code"=>"LR","en"=>"Liberia","ko"=>"라이베리아","ja"=>"リベリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LY'=>["code"=>"LY","en"=>"Libya","ko"=>"리비아","ja"=>"リビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MG'=>["code"=>"MG","en"=>"Madagascar","ko"=>"마다가스카르","ja"=>"マダガスカル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MW'=>["code"=>"MW","en"=>"Malawi","ko"=>"말라위","ja"=>"マラウイ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ML'=>["code"=>"ML","en"=>"Mali","ko"=>"말리","ja"=>"マリ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MR'=>["code"=>"MR","en"=>"Mauritania","ko"=>"모리타니","ja"=>"モーリタニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MU'=>["code"=>"MU","en"=>"Mauritius","ko"=>"모리셔스","ja"=>"モーリシャス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MA'=>["code"=>"MA","en"=>"Morocco","ko"=>"모로코","ja"=>"モロッコ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MZ'=>["code"=>"MZ","en"=>"Mozambique","ko"=>"모잠비크","ja"=>"モザンビーク","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NA'=>["code"=>"NA","en"=>"Namibia","ko"=>"나미비아","ja"=>"ナミビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NE'=>["code"=>"NE","en"=>"Niger","ko"=>"니제르","ja"=>"ニジェール","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NG'=>["code"=>"NG","en"=>"Nigeria","ko"=>"나이지리아","ja"=>"ナイジェリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'RW'=>["code"=>"RW","en"=>"Rwanda","ko"=>"르완다","ja"=>"ルワンダ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ST'=>["code"=>"ST","en"=>"Sao Tome and Principe","ko"=>"상투메 프린시페","ja"=>"サントメ・プリンシペ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SN'=>["code"=>"SN","en"=>"Senegal","ko"=>"세네갈","ja"=>"セネガル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SC'=>["code"=>"SC","en"=>"Seychelles","ko"=>"세이셸","ja"=>"セーシェル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SL'=>["code"=>"SL","en"=>"Sierra Leone","ko"=>"시에라리온","ja"=>"シエラレオネ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SO'=>["code"=>"SO","en"=>"Somalia","ko"=>"소말리아","ja"=>"ソマリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ZA'=>["code"=>"ZA","en"=>"South Africa","ko"=>"남아프리카공화국","ja"=>"南アフリカ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SS'=>["code"=>"SS","en"=>"South Sudan","ko"=>"남수단","ja"=>"南スーダン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SD'=>["code"=>"SD","en"=>"Sudan","ko"=>"수단","ja"=>"スーダン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TZ'=>["code"=>"TZ","en"=>"Tanzania","ko"=>"탄자니아","ja"=>"タンザニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TG'=>["code"=>"TG","en"=>"Togo","ko"=>"토고","ja"=>"トーゴ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TN'=>["code"=>"TN","en"=>"Tunisia","ko"=>"튀니지","ja"=>"チュニジア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'UG'=>["code"=>"UG","en"=>"Uganda","ko"=>"우간다","ja"=>"ウガンダ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ZM'=>["code"=>"ZM","en"=>"Zambia","ko"=>"잠비아","ja"=>"ザンビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'ZW'=>["code"=>"ZW","en"=>"Zimbabwe","ko"=>"짐바브웨","ja"=>"ジンバブエ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            // 기타 아프리카 해외 영토, 특수국가 필요시 추가
        ];
    }

    private function northAmericaCountries()
    {
        $manager = '북미총괄';
        $manager_email = 'northamerica_manager@example.com';
        $continent = 'North America';
        return [
            'AG'=>["code"=>"AG","en"=>"Antigua and Barbuda","ko"=>"앤티가 바부다","ja"=>"アンティグア・バーブーダ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BS'=>["code"=>"BS","en"=>"Bahamas","ko"=>"바하마","ja"=>"バハマ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BB'=>["code"=>"BB","en"=>"Barbados","ko"=>"바베이도스","ja"=>"バルバドス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BZ'=>["code"=>"BZ","en"=>"Belize","ko"=>"벨리즈","ja"=>"ベリーズ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CA'=>["code"=>"CA","en"=>"Canada","ko"=>"캐나다","ja"=>"カナダ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CR'=>["code"=>"CR","en"=>"Costa Rica","ko"=>"코스타리카","ja"=>"コスタリカ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CU'=>["code"=>"CU","en"=>"Cuba","ko"=>"쿠바","ja"=>"キューバ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'DM'=>["code"=>"DM","en"=>"Dominica","ko"=>"도미니카 연방","ja"=>"ドミニカ国","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'DO'=>["code"=>"DO","en"=>"Dominican Republic","ko"=>"도미니카 공화국","ja"=>"ドミニカ共和国","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SV'=>["code"=>"SV","en"=>"El Salvador","ko"=>"엘살바도르","ja"=>"エルサルバドル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GD'=>["code"=>"GD","en"=>"Grenada","ko"=>"그레나다","ja"=>"グレナダ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GT'=>["code"=>"GT","en"=>"Guatemala","ko"=>"과테말라","ja"=>"グアテマラ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'HT'=>["code"=>"HT","en"=>"Haiti","ko"=>"아이티","ja"=>"ハイチ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'HN'=>["code"=>"HN","en"=>"Honduras","ko"=>"온두라스","ja"=>"ホンジュラス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'JM'=>["code"=>"JM","en"=>"Jamaica","ko"=>"자메이카","ja"=>"ジャマイカ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MX'=>["code"=>"MX","en"=>"Mexico","ko"=>"멕시코","ja"=>"メキシコ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NI'=>["code"=>"NI","en"=>"Nicaragua","ko"=>"니카라과","ja"=>"ニカラグア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PA'=>["code"=>"PA","en"=>"Panama","ko"=>"파나마","ja"=>"パナマ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KN'=>["code"=>"KN","en"=>"Saint Kitts and Nevis","ko"=>"세인트키츠 네비스","ja"=>"セントクリストファー・ネイビス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'LC'=>["code"=>"LC","en"=>"Saint Lucia","ko"=>"세인트루시아","ja"=>"セントルシア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'VC'=>["code"=>"VC","en"=>"Saint Vincent and the Grenadines","ko"=>"세인트빈센트 그레나딘","ja"=>"セントビンセントおよびグレナディーン諸島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TT'=>["code"=>"TT","en"=>"Trinidad and Tobago","ko"=>"트리니다드 토바고","ja"=>"トリニダード・トバゴ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'US'=>["code"=>"US","en"=>"United States","ko"=>"미국","ja"=>"アメリカ合衆国","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
        ];
    }

    private function southAmericaCountries()
    {
        $manager = '남미총괄';
        $manager_email = 'southamerica_manager@example.com';
        $continent = 'South America';
        return [
            'AR'=>["code"=>"AR","en"=>"Argentina","ko"=>"아르헨티나","ja"=>"アルゼンチン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BO'=>["code"=>"BO","en"=>"Bolivia","ko"=>"볼리비아","ja"=>"ボリビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BR'=>["code"=>"BR","en"=>"Brazil","ko"=>"브라질","ja"=>"ブラジル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CL'=>["code"=>"CL","en"=>"Chile","ko"=>"칠레","ja"=>"チリ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CO'=>["code"=>"CO","en"=>"Colombia","ko"=>"콜롬비아","ja"=>"コロンビア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'EC'=>["code"=>"EC","en"=>"Ecuador","ko"=>"에콰도르","ja"=>"エキュベシュ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GF'=>["code"=>"GF","en"=>"French Guiana","ko"=>"프랑스령 기아나","ja"=>"フランス領ギアナ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GY'=>["code"=>"GY","en"=>"Guyana","ko"=>"가이아나","ja"=>"ガイアナ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PY'=>["code"=>"PY","en"=>"Paraguay","ko"=>"파라과이","ja"=>"パラグアイ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PE'=>["code"=>"PE","en"=>"Peru","ko"=>"페루","ja"=>"ペルー","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SR'=>["code"=>"SR","en"=>"Suriname","ko"=>"수리남","ja"=>"スリナム","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'UY'=>["code"=>"UY","en"=>"Uruguay","ko"=>"우루과이","ja"=>"ウルグアイ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'VE'=>["code"=>"VE","en"=>"Venezuela","ko"=>"베네수엘라","ja"=>"ベネズエラ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
        ];
    }

    private function oceaniaCountries()
    {
        $manager = '오세아니아총괄';
        $manager_email = 'oceania_manager@example.com';
        $continent = 'Oceania';
        return [
            'AS'=>["code"=>"AS","en"=>"American Samoa","ko"=>"아메리칸 사모아","ja"=>"アメリカンサモア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'AU'=>["code"=>"AU","en"=>"Australia","ko"=>"호주","ja"=>"オーストラリア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'CK'=>["code"=>"CK","en"=>"Cook Islands","ko"=>"쿡 제도","ja"=>"クック諸島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'FJ'=>["code"=>"FJ","en"=>"Fiji","ko"=>"피지","ja"=>"フィジー","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'FM'=>["code"=>"FM","en"=>"Micronesia","ko"=>"미크로네시아","ja"=>"ミクロネシア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GU'=>["code"=>"GU","en"=>"Guam","ko"=>"괌","ja"=>"グアム","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'KI'=>["code"=>"KI","en"=>"Kiribati","ko"=>"키리바시","ja"=>"キリバス","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MH'=>["code"=>"MH","en"=>"Marshall Islands","ko"=>"마셜 제도","ja"=>"マーシャル諸島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NR'=>["code"=>"NR","en"=>"Nauru","ko"=>"나우루","ja"=>"ナウル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NC'=>["code"=>"NC","en"=>"New Caledonia","ko"=>"뉴칼레도니아","ja"=>"ニューカレドニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NZ'=>["code"=>"NZ","en"=>"New Zealand","ko"=>"뉴질랜드","ja"=>"ニュージーランド","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NU'=>["code"=>"NU","en"=>"Niue","ko"=>"니우에","ja"=>"ニウエ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'NF'=>["code"=>"NF","en"=>"Norfolk Island","ko"=>"노퍽섬","ja"=>"ノーフォーク島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'MP'=>["code"=>"MP","en"=>"Northern Mariana Islands","ko"=>"북마리아나 제도","ja"=>"北マリアナ諸島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PW'=>["code"=>"PW","en"=>"Palau","ko"=>"팔라우","ja"=>"パラオ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PG'=>["code"=>"PG","en"=>"Papua New Guinea","ko"=>"파푸아뉴기니","ja"=>"パプアニューギニア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PN'=>["code"=>"PN","en"=>"Pitcairn Islands","ko"=>"핏케언 제도","ja"=>"ピトケアン諸島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'WS'=>["code"=>"WS","en"=>"Samoa","ko"=>"사모아","ja"=>"サモア","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SB'=>["code"=>"SB","en"=>"Solomon Islands","ko"=>"솔로몬 제도","ja"=>"ソロモン諸島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TK'=>["code"=>"TK","en"=>"Tokelau","ko"=>"토켈라우","ja"=>"トケラウ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TO'=>["code"=>"TO","en"=>"Tonga","ko"=>"통가","ja"=>"トンガ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TV'=>["code"=>"TV","en"=>"Tuvalu","ko"=>"투발루","ja"=>"ツバル","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'VU'=>["code"=>"VU","en"=>"Vanuatu","ko"=>"바누아투","ja"=>"バヌアツ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
        ];
    }

    private function otherTerritories()
    {
        $manager = '기타특수총괄';
        $manager_email = 'other_manager@example.com';
        $continent = 'Other';
        return [
            'AQ'=>["code"=>"AQ","en"=>"Antarctica","ko"=>"남극","ja"=>"南極","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'BV'=>["code"=>"BV","en"=>"Bouvet Island","ko"=>"부베섬","ja"=>"ブーベ島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'HM'=>["code"=>"HM","en"=>"Heard Island and McDonald Islands","ko"=>"허드 맥도널드 제도","ja"=>"ハード島とマクドナルド諸島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'TF'=>["code"=>"TF","en"=>"French Southern Territories","ko"=>"프랑스령 남부와 남극 지역","ja"=>"フランス領南方・南極地域","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GS'=>["code"=>"GS","en"=>"South Georgia and the South Sandwich Islands","ko"=>"사우스조지아 사우스샌드위치 제도","ja"=>"サウスジョージア・サウスサンドウィッチ諸島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'UM'=>["code"=>"UM","en"=>"United States Minor Outlying Islands","ko"=>"미국령 해외 소속 제도","ja"=>"アメリカ領海外領土","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'IO'=>["code"=>"IO","en"=>"British Indian Ocean Territory","ko"=>"영국령 인도양 지역","ja"=>"イギリス領インド洋地域","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'SJ'=>["code"=>"SJ","en"=>"Svalbard and Jan Mayen","ko"=>"스발바르 얀마옌","ja"=>"スバールバル諸島およびヤンマイエン島","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'YT'=>["code"=>"YT","en"=>"Mayotte","ko"=>"마요트","ja"=>"マヨット","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'RE'=>["code"=>"RE","en"=>"Réunion","ko"=>"레위니옹","ja"=>"レユニオン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'PM'=>["code"=>"PM","en"=>"Saint Pierre and Miquelon","ko"=>"생피에르 미클롱","ja"=>"サンピエール・ミクロン","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
            'GF'=>["code"=>"GF","en"=>"French Guiana","ko"=>"프랑스령 기아나","ja"=>"フランス領ギアナ","continent"=>$continent,"continent_manager"=>$manager,"continent_manager_email"=>$manager_email],
        ];
    }
}
