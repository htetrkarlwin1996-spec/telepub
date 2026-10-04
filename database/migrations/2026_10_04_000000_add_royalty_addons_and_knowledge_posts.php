<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albums', fn (Blueprint $table) => $table->json('selected_addons')->nullable()->after('selected_store_ids'));
        Schema::table('release_payments', fn (Blueprint $table) => $table->json('addon_services')->nullable()->after('currency'));
        Schema::create('knowledge_posts', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique(); $table->text('excerpt');
            $table->longText('body'); $table->string('cover_image')->nullable(); $table->boolean('is_published')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0); $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });

        foreach (['addon_composer_songwriter_usd' => '0', 'addon_global_performance_usd' => '0', 'addon_mechanical_usd' => '0'] as $key => $value) {
            DB::table('app_settings')->insertOrIgnore(['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }

        $posts = [
            ['Master Royalties (Sound Recording Share)', 'Master Royalty သည် သီချင်း၏ မူရင်း Audio Recording ပေါ်မှ ရရှိသောဝင်ငွေဖြစ်သည်။', "Master Royalty ဆိုသည်မှာ အဘယ်နည်း?\n\nMaster Royalty (Sound Recording Royalty) သည် သီချင်းတစ်ပုဒ်၏ အသံသွင်းထားသော မူရင်း Audio File (Master Recording) ပေါ်တွင် မူပိုင်ခွင့်ရှိသူများအတွက် ရရှိသည့် အခြေခံဝင်ငွေဖြစ်သည်။ Singer/Vocalist၊ Record Producer နှင့် Record Label/Agency တို့က အဓိကပိုင်ဆိုင်ကြသည်။\n\nTeleMusic, LLC မှ ကောက်ယူပေးသည့် နယ်ပယ်များ\n\n• Digital Interactive Streaming — Spotify, Apple Music, YouTube Music, Amazon Music, Tidal နှင့် Deezer တို့မှ Master Share ဝင်ငွေ။\n\n• Digital Permanent Downloads — iTunes Store နှင့် Amazon MP3 တို့မှ သီချင်း သို့မဟုတ် Album ဝယ်ယူ Download လုပ်သည့်ဝင်ငွေ။\n\n• Social Platforms & Short Video Content (Micro-Sync) — TikTok, Instagram Reels, Facebook Stories နှင့် YouTube Shorts တို့တွင် Master Audio အသုံးပြုမှုမှ ဝင်ငွေ။", 'images/knowledge/master-royalties.jpg'],
            ['Mechanical Royalties (Streaming & Downloads)', 'Streaming နှင့် download ပြုလုပ်တိုင်း Songwriter/Composer များအတွက် ရရှိနိုင်သည့် royalty ဖြစ်သည်။', "Mechanical Royalty ဆိုသည်မှာ အဘယ်နည်း?\n\nသီချင်း၏ စာသားနှင့် သံစဉ် (Composition) ကို Physical သို့မဟုတ် Digital နည်းလမ်းဖြင့် ကူးယူဖန်တီး၊ ဖြန့်ဖြူးခွင့်အတွက် Songwriters/Composers ထံပေးချေရသည့် မူပိုင်ခွင့်ဝင်ငွေဖြစ်သည်။\n\nTeleMusic, LLC မှ ကောက်ယူပေးသည့် နယ်ပယ်များ\n\n• Digital Interactive Streaming Mechanicals — Spotify, Apple Music စသည့် platform များမှ royalty ကို MLC, MCPS နှင့် ကမ္ဘာတစ်ဝှမ်းရှိ Mechanical Licensing Societies များထံမှ ကောက်ယူပေးသည်။\n\n• Digital Download Mechanicals — iTunes သို့မဟုတ် Amazon တွင် သီချင်းဝယ်ယူ Download လုပ်တိုင်း Statutory Mechanical Rate အတိုင်း ကောက်ယူပေးသည်။", 'images/knowledge/mechanical-royalties.jpg'],
            ['Composer / Songwriter Royalties (Composition Share)', 'Lyricist၊ Composer နှင့် Beatmaker/Producer တို့၏ Composition Rights ဝင်ငွေကို နားလည်နိုင်မည့်လမ်းညွှန်။', "Composer / Songwriter Royalty ဆိုသည်မှာ အဘယ်နည်း?\n\nသီချင်း၏ Lyrics၊ Melody နှင့် Composition တို့ကို ဖန်တီးခဲ့သည့် Lyricist၊ Composer နှင့် Beatmaker/Producer တို့အတွက် ရသင့်သည့် Composition Rights ဝင်ငွေဖြစ်သည်။\n\nTeleMusic, LLC မှ ကောက်ယူပေးသည့် နယ်ပယ်များ\n\n• Lyricist Share — စာသားရေးသူအတွက် သတ်မှတ်ထားသည့် ရာခိုင်နှုန်းအလိုက် ဝင်ငွေခွဲဝေကောက်ယူခြင်း။\n\n• Composer / Beatmaker Share — Melody, Chord structure သို့မဟုတ် Instrumental Beat ဖန်တီးသူအတွက် Composition Share ကောက်ယူခြင်း။\n\n• Composition Split Management — ပူးပေါင်းရေးသားသူများ၏ သဘောတူရာခိုင်နှုန်းအတိုင်း တရားဝင်စာရင်းသွင်း၍ ဝင်ငွေကို တိကျစွာခွဲဝေပေးခြင်း။", 'images/knowledge/composer-songwriter.jpg'],
            ['Global Performance Royalties (Public Performance Share)', 'Radio၊ TV၊ live concert နှင့် public venues များတွင် သီချင်းအသုံးပြုမှုမှ ရရှိသည့်ဝင်ငွေ။', "Global Performance Royalty ဆိုသည်မှာ အဘယ်နည်း?\n\nသီချင်း၏ Composition ကို အများပြည်သူဆိုင်ရာနေရာများတွင် ဖွင့်လှစ်ခြင်း၊ ထုတ်လွှင့်ခြင်း သို့မဟုတ် တိုက်ရိုက်ဖျော်ဖြေခြင်းအတွက် Songwriter နှင့် Publisher တို့ထံပေးချေရသည့်ဝင်ငွေဖြစ်သည်။\n\nTeleMusic, LLC မှ ကောက်ယူပေးသည့် နယ်ပယ်များ\n\n• Terrestrial Radio & TV Broadcasting — ကမ္ဘာတစ်ဝှမ်းရှိ Radio, TV, ကြော်ငြာနှင့် TV Series များမှ Performance Royalty။\n\n• Live Concerts & Public Venues — Concert, club, restaurant, shopping mall နှင့် public event များမှ ဝင်ငွေ။\n\n• Non-Interactive & Satellite Radio — Pandora, SiriusXM နှင့် streaming radio များမှ ဝင်ငွေကို BMI, ASCAP, PRS, SACEM စသည့် PRO အဖွဲ့အစည်းများနှင့် ချိတ်ဆက်ကောက်ယူပေးသည်။", 'images/knowledge/global-performance.jpg'],
            ['Sync Licensing & Secondary Rights', 'ရုပ်ရှင်၊ TV Series၊ Video Game၊ ကြော်ငြာနှင့် YouTube တို့တွင် သီချင်းအသုံးပြုခွင့်ဆိုင်ရာ လမ်းညွှန်။', "Sync Licensing ဆိုသည်မှာ အဘယ်နည်း?\n\nသီချင်း၏ အသံနှင့် သံစဉ်ကို ရုပ်ရှင်၊ TV Series၊ Video Games နှင့် ကြော်ငြာများ၏ ရုပ်ပုံ/ဗီဒီယိုနှင့် တွဲဖက်အသုံးပြုခွင့်ပေးခြင်းအတွက် ရရှိသည့် လိုင်စင်ကြေးဖြစ်သည်။\n\nTeleMusic, LLC မှ ကောက်ယူပေးသည့် နယ်ပယ်များ\n\n• Sync Upfront Licensing Fees — ရုပ်ရှင်၊ ကြော်ငြာ သို့မဟုတ် Game ထုတ်လုပ်သူများထံမှ သီချင်းအသုံးပြုခွင့် လိုင်စင်ကြေးရယူပေးခြင်း။\n\n• YouTube Publishing Micro-Sync — Content Creator များက Composition ကိုအသုံးပြုထားသည့် video များတွင် copyright claim ပြုလုပ်၍ Publishing Share ကောက်ယူခြင်း။\n\nTeleMusic, LLC သည် Master Recording Share သာမက Mechanical, Composer / Songwriter, Global Performance နှင့် Sync Fees များကိုပါ ကမ္ဘာတစ်ဝှမ်းမှ စနစ်တကျကောက်ယူပေးသည်။", 'images/knowledge/sync-licensing.jpg'],
        ];
        foreach ($posts as $index => [$title, $excerpt, $body, $cover]) DB::table('knowledge_posts')->insert(['title' => $title, 'slug' => Str::slug($title), 'excerpt' => $excerpt, 'body' => $body, 'cover_image' => $cover, 'is_published' => true, 'sort_order' => $index + 1, 'published_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_posts');
        Schema::table('release_payments', fn (Blueprint $table) => $table->dropColumn('addon_services'));
        Schema::table('albums', fn (Blueprint $table) => $table->dropColumn('selected_addons'));
        DB::table('app_settings')->whereIn('key', ['addon_composer_songwriter_usd', 'addon_global_performance_usd', 'addon_mechanical_usd'])->delete();
    }
};
