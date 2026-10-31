from pathlib import Path

import arabic_reshaper
from bidi.algorithm import get_display
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4, landscape
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfgen import canvas


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "output" / "pdf"
OUT.mkdir(parents=True, exist_ok=True)
FONT_DIR = Path(r"C:\Windows\Fonts")
pdfmetrics.registerFont(TTFont("Arial", str(FONT_DIR / "arial.ttf")))
pdfmetrics.registerFont(TTFont("Arial-Bold", str(FONT_DIR / "arialbd.ttf")))

NAVY = colors.HexColor("#172554")
BLUE = colors.HexColor("#2866e8")
PALE = colors.HexColor("#eff5ff")
INK = colors.HexColor("#1f2937")
MUTED = colors.HexColor("#64748b")
LINE = colors.HexColor("#cbdcf8")


def visual(text: str) -> str:
    return get_display(arabic_reshaper.reshape(text))


def wrap_logical(text: str, width: float, font: str, size: float) -> list[str]:
    words = text.split()
    lines: list[str] = []
    current = ""
    for word in words:
        proposed = f"{current} {word}".strip()
        if current and pdfmetrics.stringWidth(visual(proposed), font, size) > width:
            lines.append(current)
            current = word
        else:
            current = proposed
    if current:
        lines.append(current)
    return lines or [""]


class SrsWriter:
    def __init__(self, path: Path):
        self.canvas = canvas.Canvas(str(path), pagesize=A4, pageCompression=1)
        self.canvas.setTitle("Wasl Software Requirements Specification")
        self.canvas.setAuthor("Wasl Project Team")
        self.width, self.height = A4
        self.page = 0
        self.y = 0
        self.new_page()

    def new_page(self):
        if self.page:
            self.canvas.setStrokeColor(LINE)
            self.canvas.line(42, 38, self.width - 42, 38)
            self.canvas.setFont("Arial", 8)
            self.canvas.setFillColor(MUTED)
            self.canvas.drawRightString(self.width - 42, 24, visual(f"وَصْل | وثيقة متطلبات البرمجيات | {self.page}"))
            self.canvas.showPage()
        self.page += 1
        self.y = self.height - 54
        self.canvas.setFillColor(BLUE)
        self.canvas.roundRect(42, self.height - 82, self.width - 84, 38, 12, fill=1, stroke=0)
        self.canvas.setFillColor(colors.white)
        self.canvas.setFont("Arial-Bold", 12)
        self.canvas.drawRightString(self.width - 58, self.height - 68, visual("وَصْل | مكتبة معرفية مترابطة"))
        self.y = self.height - 112

    def ensure(self, height: float):
        if self.y - height < 55:
            self.new_page()

    def title(self, text: str, size: int = 18):
        self.ensure(42)
        self.canvas.setFillColor(NAVY)
        self.canvas.setFont("Arial-Bold", size)
        self.canvas.drawRightString(self.width - 48, self.y, visual(text))
        self.y -= size + 12

    def paragraph(self, text: str, size: int = 10, leading: int = 16, color=INK, indent=0):
        max_width = self.width - 96 - indent
        lines = wrap_logical(text, max_width, "Arial", size)
        self.ensure(len(lines) * leading + 5)
        self.canvas.setFillColor(color)
        self.canvas.setFont("Arial", size)
        for line in lines:
            self.canvas.drawRightString(self.width - 48 - indent, self.y, visual(line))
            self.y -= leading
        self.y -= 4

    def bullet(self, text: str, size: int = 9, leading: int = 14):
        self.paragraph("• " + text, size=size, leading=leading, indent=10)

    def section(self, text: str):
        self.ensure(34)
        self.canvas.setFillColor(BLUE)
        self.canvas.setFont("Arial-Bold", 12)
        self.canvas.drawRightString(self.width - 48, self.y, visual(text))
        self.y -= 22

    def finish(self):
        self.canvas.setStrokeColor(LINE)
        self.canvas.line(42, 38, self.width - 42, 38)
        self.canvas.setFont("Arial", 8)
        self.canvas.setFillColor(MUTED)
        self.canvas.drawRightString(self.width - 42, 24, visual(f"وَصْل | وثيقة متطلبات البرمجيات | {self.page}"))
        self.canvas.save()


def create_srs():
    writer = SrsWriter(OUT / "Wasl_SRS.pdf")
    writer.title("وثيقة متطلبات البرمجيات", 21)
    writer.paragraph("الإصدار 2.0 | مراجعة المشروع النهائي | 31 أكتوبر 2026", 10, color=MUTED)
    writer.section("1. الملخص والنطاق")
    writer.paragraph("وَصْل منصة ويب عربية لحفظ الاقتباسات والأفكار والملاحظات في بطاقات دائمة، تصنيفها وربطها واستكشاف العلاقات بينها. تعرض المنصة البطاقات العامة للزوار، وتتيح للأعضاء إدارة مكتباتهم الخاصة.")
    writer.section("2. الأدوار")
    writer.bullet("الزائر: تصفح البطاقات العامة والبحث والتصفية ومشاهدة اسم الناشر وتاريخ النشر واستكشاف الشبكة.")
    writer.bullet("العضو: إضافة البطاقات وإدارة بطاقاته فقط، تحديد الخصوصية، ربط البطاقات والإعجاب وإعادة النشر.")
    writer.section("3. المتطلبات الوظيفية")
    requirements = [
        "FR-01 عرض البطاقات العامة من دون تسجيل الدخول.",
        "FR-02 إظهار محتوى البطاقة واسم صاحبها وتاريخ نشرها.",
        "FR-03 إنشاء حساب باسم من 2 إلى 50 حرفاً وبريد صالح وكلمة مرور لا تقل عن 8 أحرف؛ تُخزّن كلمة المرور مجزأة.",
        "FR-04 تسجيل الدخول والخروج وتجديد الجلسة عند المصادقة.",
        "FR-05 إنشاء اقتباس من 3 إلى 155 حرفاً.",
        "FR-06 إنشاء فكرة أو ملاحظة من 3 إلى 2000 حرف.",
        "FR-07 إرفاق مجال ومصدر ووسوم وملاحظة شخصية بالبطاقة.",
        "FR-08 جعل البطاقة عامة أو خاصة، وإخفاء الخاصة عن غير مالكها.",
        "FR-09 السماح للمالك وحده بتعديل بطاقته أو حذفها.",
        "FR-10 البحث والتصفية بالنص والنوع والمجال، وعرض 12 بطاقة في الصفحة.",
        "FR-11 الإعجاب وإعادة النشر للأعضاء المسجلين ومنع تكرار التفاعل.",
        "FR-12 ربط البطاقات بعلاقات معنونة وعرضها في شبكة المعرفة.",
        "FR-13 حساب المؤشرات والمخططات من البطاقات العامة المخزنة فعلياً.",
    ]
    for item in requirements:
        writer.bullet(item)

    writer.section("4. المتطلبات غير الوظيفية")
    writer.bullet("الأمان: تحقق خادمي، تجزئة كلمات المرور، حماية CSRF والجلسات، وتفويض عمليات البطاقة بحسب المالك.")
    writer.bullet("الخصوصية: لا تظهر البطاقات الخاصة في المكتبة العامة أو البحث أو المؤشرات أو شبكة أعضاء آخرين.")
    writer.bullet("سهولة الاستخدام: واجهة عربية من اليمين إلى اليسار، رسائل تحقق، تصميم متجاوب، وتقليل الحركة وفق إعداد الجهاز.")
    writer.bullet("الأداء: 12 بطاقة لكل صفحة وتحميل مسبق للعلاقات. هدف الاستجابة أقل من 3 ثوانٍ يحتاج قياساً على بيئة اللجنة وبيانات بحجم الإنتاج.")
    writer.bullet("الاستمرارية: حفظ المحتوى والعلاقات والتفاعلات في قاعدة البيانات باستخدام Laravel migrations.")

    writer.section("5. الإضافات")
    writer.paragraph("تتجاوز المنصة الاقتباسات بإضافة الأفكار والملاحظات والخصوصية والوسوم والبحث والتصفية والإعجاب وإعادة النشر. ويمكن وصل البطاقات بعلاقات «ترتبط» و«تدعم» و«تتحدى» و«توسّع» وعرضها على شكل خريطة شبكية.")

    writer.section("6. البيانات")
    writer.paragraph("الكيانات الأساسية: المستخدمون، البطاقات، وصلات البطاقات، الإعجابات، وإعادات النشر. يحوي ملف Wasl_ERD.pdf مخطط العلاقات والحقول الرئيسية.")

    writer.section("7. مراجعة التنفيذ")
    checks = [
        "نجحت 9 اختبارات Feature في 2.34 ثانية، مع 53 تحققاً، وتغطي التصفح والتسجيل والتحقق والخصوصية والملكية والصفحات والشبكة والتفاعلات.",
        "احتاجت جلسة التجهيز إلى تشغيل Pest بإعداد bootstrap بديل لأن فحص is_readable لملفات المشروع محجوب في بيئة sandbox؛ هذا قيد في بيئة التجهيز.",
        "بناء الواجهة: npm run build ناجح.",
        "فحص الأداء حتى 3 ثوانٍ لم يُقَس بعد؛ يعتمد على جهاز اللجنة وحجم البيانات.",
    ]
    for item in checks:
        writer.bullet(item)

    writer.section("8. التشغيل والتسليم")
    writer.paragraph("تعليمات التشغيل في README.md. حزمة المصدر تستثني .env وvendor وnode_modules وملف قاعدة البيانات المحلي؛ تُنشأ قاعدة جديدة عبر الترحيلات. سيناريو الفيديو في docs/demo-script.md، ويبقى تسجيله ورفع رابطه خطوة خارجية.")
    writer.section("9. قائمة تحقق قبل العرض")
    for item in [
        "تشغيل composer install وnpm install ثم ترحيلات قاعدة البيانات وبناء الأصول.",
        "تشغيل php artisan test وحفظ النتيجة بعد التأكد من أن ملف البيئة وقاعدة البيانات لا يحتويان معلومات شخصية.",
        "تجربة حسابين: التأكد من أن البطاقة الخاصة لا تظهر للزائر أو العضو الآخر، وأن التعديل والحذف للمالك فقط.",
        "إضافة بطاقات مترابطة، فتح خريطة الشبكة، وتجربة البحث والتصفية والإعجاب وإعادة النشر.",
        "قياس زمن الاستجابة في بيئة العرض، ثم تسجيل الفيديو ورفع رابطه إلى جهة التسليم.",
    ]:
        writer.bullet(item)
    writer.finish()


def draw_entity(c, x, y, w, title, fields):
    header_h = 27
    line_h = 17
    h = header_h + line_h * len(fields) + 12
    c.setFillColor(colors.white)
    c.setStrokeColor(BLUE)
    c.setLineWidth(1.2)
    c.roundRect(x, y, w, h, 8, fill=1, stroke=1)
    c.setFillColor(PALE)
    c.roundRect(x, y + h - header_h, w, header_h, 8, fill=1, stroke=0)
    c.setFillColor(NAVY)
    c.setFont("Arial-Bold", 10)
    c.drawCentredString(x + w / 2, y + h - 18, title)
    c.setFillColor(INK)
    c.setFont("Arial", 8)
    current_y = y + h - header_h - 15
    for field in fields:
        c.drawString(x + 10, current_y, field)
        current_y -= line_h
    return h


def create_erd():
    path = OUT / "Wasl_ERD.pdf"
    page = landscape(A4)
    c = canvas.Canvas(str(path), pagesize=page, pageCompression=1)
    c.setTitle("Wasl Entity Relationship Diagram")
    c.setAuthor("Wasl Project Team")
    width, height = page
    c.setFillColor(BLUE)
    c.roundRect(32, height - 60, width - 64, 35, 10, fill=1, stroke=0)
    c.setFillColor(colors.white)
    c.setFont("Arial-Bold", 13)
    c.drawRightString(width - 48, height - 48, visual("مخطط قاعدة بيانات وَصْل | الكيانات والعلاقات"))

    # Place the core entities across the upper row and interactions below.
    users_h = draw_entity(c, 38, 300, 190, "USERS", ["id (PK)", "name", "email (unique)", "password (hashed)", "timestamps"])
    quotes_h = draw_entity(c, 315, 270, 230, "QUOTES", ["id (PK)", "user_id (FK)", "text, kind, category", "source, note, tags", "visibility, timestamps"])
    links_h = draw_entity(c, 614, 300, 190, "QUOTE_LINKS", ["quote_id (PK, FK)", "related_quote_id (PK, FK)", "relation_type", "timestamps"])
    likes_h = draw_entity(c, 205, 80, 185, "QUOTE_LIKES", ["quote_id (PK, FK)", "user_id (PK, FK)", "timestamps"])
    repost_h = draw_entity(c, 465, 80, 200, "QUOTE_REPOSTS", ["quote_id (PK, FK)", "user_id (PK, FK)", "timestamps"])

    c.setStrokeColor(MUTED)
    c.setLineWidth(1.3)
    # Relationship connectors and labels.
    c.line(228, 365, 315, 365)
    c.line(545, 365, 614, 365)
    c.line(430, 270, 614, 330)
    c.line(160, 300, 245, 80 + likes_h - 17)
    c.line(195, 300, 505, 80 + repost_h - 17)
    c.line(370, 270, 335, 80 + likes_h - 17)
    c.line(390, 270, 530, 80 + repost_h - 17)

    for x, y, text in [
        (240, 372, "1  :  many"),
        (548, 372, "card links"),
        (486, 327, "self relation"),
        (175, 190, "member"),
        (500, 190, "member"),
    ]:
        c.setFillColor(MUTED)
        c.setFont("Arial", 7)
        c.drawCentredString(x, y, text)

    c.setFillColor(INK)
    c.setFont("Arial-Bold", 9)
    c.drawRightString(width - 42, 54, visual("مفتاح: PK مفتاح أساسي | FK مفتاح أجنبي | القيد المركب يمنع تكرار الإعجاب أو إعادة النشر."))
    c.setFillColor(MUTED)
    c.setFont("Arial", 8)
    c.drawRightString(width - 42, 35, visual("حذف المستخدم يحذف بطاقاته وتفاعلاتها ووصلاتها التابعة عبر قيود الحذف المتسلسل."))
    c.save()


if __name__ == "__main__":
    create_srs()
    create_erd()
    print(f"Created {OUT / 'Wasl_SRS.pdf'}")
    print(f"Created {OUT / 'Wasl_ERD.pdf'}")
