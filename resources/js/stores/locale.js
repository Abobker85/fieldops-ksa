import { defineStore } from 'pinia';

const messages = {
  ar: {
    common: {
      appName: 'FieldOps KSA',
      appTagline: 'منصة إدارة العمليات الميدانية للمقاولات',
      tenantFallback: 'منصة المقاولات',
      logout: 'تسجيل الخروج',
      switchLanguage: 'English',
      currentLangLabel: 'العربية',
      cancel: 'إلغاء',
      save: 'حفظ',
      saving: 'جاري الحفظ...',
      close: 'إغلاق',
      search: 'بحث...',
      all: 'الكل',
      loading: 'جاري التحميل...',
      actions: 'الإجراء',
      details: 'التفاصيل',
      sar: 'ر.س',
      error: 'خطأ',
      viewFile: 'عرض الملف',
      delete: 'حذف',
      edit: 'تعديل',
      saveFailed: 'فشل الحفظ',
      success: 'تم بنجاح',
      footer: 'منصة FieldOps KSA لإدارة العمليات الميدانية • التوثيق الفوري وحساب الإنجاز الموزون للمقاولات',
    },
    roles: {
      owner: 'المدير العام',
      pm: 'مدير المشاريع',
      site_engineer: 'مهندس الموقع',
      viewer: 'استشاري / مالك',
    },
    disciplines: {
      all: 'جميع التخصصات',
      architectural: 'معماري',
      structural: 'إنشائي',
      mechanical: 'ميكانيكا',
      electrical: 'كهرباء',
      contracts: 'عقود',
      permits: 'تراخيص',
    },
    requestTypes: {
      WIR: 'طلب استلام أعمال (WIR)',
      RFI: 'طلب استفسار فني (RFI)',
      VARIATION_ORDER: 'أمر تغيير (VO)',
    },
    statuses: {
      active: 'نشط ميدانياً',
      pending: 'قيد الانتظار',
      approved: 'معتمد',
      rejected: 'مرفوض',
      under_review: 'قيد المراجعة',
      completed: 'مكتمل',
      submitted: 'مقدم',
      certified: 'معتمد للصرف',
      paid_partially: 'مصروف جزئياً',
      paid: 'مدفوع بالكامل',
    },
    login: {
      title: 'FieldOps KSA',
      subtitle: 'منصة إدارة العمليات الميدانية وتوثيق المشاريع للمقاولات',
      email: 'البريد الإلكتروني',
      emailPlaceholder: 'name@company.sa',
      password: 'كلمة المرور',
      passwordPlaceholder: '••••••••',
      submit: 'تسجيل الدخول إلى الميدان',
      submitting: 'جاري التحقق...',
      quickAccess: 'أو اختر حساباً تجريبياً للدخول الفوري:',
      ownerTitle: 'المدير العام',
      ownerSubtitle: 'إشراف وصلاحيات كاملة',
      pmTitle: 'مدير المشاريع',
      pmSubtitle: 'إدارة العمليات والمقايسات',
      engineerTitle: 'مهندس الموقع',
      engineerSubtitle: 'تسجيل اليوميات واستلام الأعمال',
      consultantTitle: 'استشاري المالك',
      consultantSubtitle: 'اعتماد المخططات والطلبات',
      failed: 'فشل تسجيل الدخول. يرجى التحقق من صحة البيانات.',
    },
    projects: {
      activeProjects: 'المشاريع النشطة',
      inProgress: 'قيد العمل',
      avgProgress: 'متوسط الإنجاز الموزون',
      onSite: 'ميداني',
      totalContractValue: 'إجمالي قيمة العقود',
      dailyReportsCount: 'التقارير اليومية المسجلة',
      reportsUnit: 'تقرير',
      searchPlaceholder: 'بحث باسم المشروع أو الكود أو المدينة...',
      newProject: 'إضافة مشروع جديد',
      loading: 'جاري تحميل بيانات المشاريع الميدانية...',
      emptyTitle: 'لم يتم العثور على مشاريع',
      emptyDesc: 'ابدأ بإنشاء مشروع جديد لإدارة العمليات الميدانية واليوميات.',
      client: 'المالك:',
      city: 'المدينة:',
      contractValue: 'قيمة العقد:',
      consultant: 'الاستشاري:',
      notSpecified: 'غير محدد',
      weightedProgressBoq: 'نسبة الإنجاز الموزونة (BOQ)',
      dailyReports: 'يوميات',
      drawings: 'مخططات',
      requests: 'طلبات',
      viewOperations: 'عرض العمليات',
      modal: {
        title: 'إنشاء مشروع ميداني جديد',
        code: 'كود المشروع',
        name: 'اسم المشروع',
        clientName: 'اسم المالك / العميل',
        consultantName: 'المكتب الاستشاري',
        city: 'المدينة',
        contractValue: 'قيمة العقد (SAR)',
        startDate: 'تاريخ البدء',
        expectedEndDate: 'تاريخ الانتهاء المتوقع',
        saving: 'جاري الحفظ...',
        saveBtn: 'حفظ وتفعيل المشروع',
      },
      createFailed: 'فشل إنشاء المشروع',
    },
    detail: {
      backToProjects: 'العودة للمشاريع',
      executivePulse: 'نبض المالك',
      executivePulseBadge: 'Executive Pulse',
      notFound: 'المشروع غير موجود',
      backHome: 'العودة للرئيسية',
      loading: 'جاري تحميل بيانات العمليات الميدانية للمشروع...',
      activeSite: 'موقع نشط',
      client: 'المالك:',
      consultant: 'الاستشاري:',
      city: 'المدينة:',
      contractValue: 'قيمة العقد:',
      weightedProgressLabel: 'نسبة الإنجاز الموزونة',
      calculatedFromBoq: 'محسوبة من المقايسة',
      liveFieldUpdate: 'تحديث حي وميداني',
      tabs: {
        reports: 'اليوميات الميدانية',
        boq: 'المقايسة والإنجاز',
        drawings: 'المخططات والوثائق',
        requests: 'الطلبات الهندسية',
        claims: 'المستخلصات',
      },
      boq: {
        title: 'جدول الكميات والمقايسة التقديرية (BOQ)',
        subtitle: 'حساب النسبة الكلية الموزونة للمشروع تلقائياً عند تعديل أي بند',
        addItem: 'إضافة بند مقايسة',
        code: 'الكود',
        description: 'بيان الأعمال',
        unit: 'الوحدة',
        quantity: 'الكمية',
        unitPrice: 'سعر الوحدة',
        total: 'الإجمالي (SAR)',
        weight: 'الوزن النسبي',
        fieldProgress: 'نسبة الإنجاز الميداني',
        modalTitle: 'إضافة بند مقايسة جديد (BOQ Item)',
        itemCode: 'كود البند',
        descriptionOfWork: 'بيان ووصف الأعمال',
        descPlaceholder: 'وصف تفصيلي لبند المقايسة والمواصفات...',
        totalQuantity: 'إجمالي الكمية',
        unitPriceLabel: 'سعر الوحدة (SAR)',
        weightPercentage: 'الوزن النسبي (%)',
        totalEstimated: 'إجمالي قيمة البند التقديرية:',
        saveItem: 'حفظ البند في المقايسة',
        adding: 'جاري الإضافة...',
        units: {
          m2: 'م2',
          m3: 'م3',
          lm: 'متر طولي',
          ton: 'طن',
          qty: 'عدد',
          lumpsum: 'مقطوعية',
          hour: 'ساعة',
        },
        progressFailed: 'فشل تحديث نسبة الإنجاز',
        addFailed: 'فشل إضافة بند المقايسة',
      },
      reports: {
        quickReport: 'إدخال تقرير يومي سريع',
        captureMedia: 'التقاط / رفع صور للموقع الميداني',
        visualStreamTitle: 'المعرض الميداني الحي',
        visualStreamSubtitle: 'أحدث صور التوثيق الميداني المباشر من الموقع',
        sitePhoto: 'صورة موقع',
        historyTitle: 'سجل التقارير الميدانية السابقة',
        empty: 'لا توجد تقارير مسجلة حتى الآن. ابدأ بإدخال تقرير اليوم!',
        approve: 'اعتماد التقرير',
        downloadPdf: 'تحميل PDF',
        shareWhatsApp: 'مشاركة واتساب',
        manpower: 'حجم العمالة:',
        workersUnit: 'عامل وفني',
        weather: 'الطقس:',
        weatherDefault: 'معتدل',
        engineer: 'المهندس المسجل:',
        workSummary: 'ملخص الأعمال اليومية:',
        blockers: 'معوقات الموقع:',
        confirmApprove: 'هل أنت متأكد من اعتماد هذا التقرير اليومي رسمياً؟',
        approveFailed: 'فشل اعتماد التقرير',
        saveFailed: 'فشل حفظ التقرير اليومي',
        needReportFirst: 'يرجى تسجيل تقرير يومي أولاً لربط الصور به.',
      },
      reportModal: {
        title: 'إدخال التقرير الميداني اليومي',
        date: 'تاريخ التقرير',
        manpower: 'عدد العمالة الميدانية',
        weather: 'حالة الطقس ودرجة الحرارة',
        summary: 'ملخص الأعمال اليومية المنفذة',
        summaryPlaceholder: 'اكتب ما تم إنجازه اليوم بالتفصيل...',
        blockers: 'معوقات وملاحظات الموقع (اختياري)',
        blockersPlaceholder: 'أي تأخير أو عوائق في التوريد أو الموقع...',
        submit: 'حفظ التقرير وإرساله',
        submitting: 'جاري الحفظ...',
      },
      mediaModal: {
        title: 'رفع صورة للموقع الميداني',
        selectReport: 'حدد التقرير اليومي المرتبط',
        reportPrefix: 'تقرير يوم:',
        file: 'ملف الصورة / التقاط بالكاميرا',
        caption: 'تعليق توضيحي على الصورة',
        captionPlaceholder: 'مثال: صب خرسانة القواعد المسلحة',
        gpsCaptured: 'تم التقاط إحداثيات الموقع بنجاح:',
        uploading: 'جاري الرفع...',
        submit: 'رفع وأرشفة الصورة',
        uploadFailed: 'فشل رفع صورة الموقع',
      },
      pulse: {
        title: 'الملخص التنفيذي ونبض المشروع',
        actualProgress: 'نسبة الإنجاز الفعلية:',
        latestManpower: 'عمالة اليوم / الأحدث:',
        workers: 'فرد',
        totalClaimed: 'إجمالي المطالبات المرفوعة:',
        approvedClaims: 'المبالغ المعتمدة:',
        latestReport: 'آخر تقرير ميداني',
        failed: 'فشل تحميل الملخص التنفيذي',
      },
      drawings: {
        title: 'مستودع المخططات والمستندات (Revision Vault)',
        subtitle: 'المخططات المعتمدة للتنفيذ (IFC) وتتبع الإصدارات',
        uploadBtn: 'رفع مخطط جديد',
        code: 'الكود',
        titleCol: 'عنوان المخطط',
        discipline: 'التخصص',
        revision: 'رقم المراجعة',
        status: 'حالة الاعتماد',
        user: 'المستخدم',
        ifcApproved: 'معتمد للبناء (IFC)',
        pendingApproval: 'قيد الاعتماد',
        uploadModalTitle: 'رفع مخطط أو مستند هندسي جديد',
        docTitle: 'عنوان المخطط / المستند',
        docCode: 'كود المخطط',
        disciplineLabel: 'التخصص',
        revisionLabel: 'رقم المراجعة (Revision)',
        ifcCheckbox: 'مخطط معتمد للتنفيذ والبناء (Issued For Construction - IFC)',
        ifcNotice: '(يتطلب صلاحية مدير المشروع أو المالك للاعتماد)',
        fileLabel: 'ملف المخطط (PDF, DWG, DXF, إلخ)',
        uploading: 'جاري الرفع...',
        submit: 'رفع وأرشفة المخطط',
        selectFile: 'يرجى اختيار ملف المخطط للرفع',
        uploadFailed: 'فشل رفع المخطط الهندسي',
      },
      requests: {
        title: 'الطلبات الهندسية والموقع (WIR / RFI / أوامر التغيير)',
        subtitle: 'استلام الأعمال الميدانية والاستفسارات الفنية',
        submitBtn: 'تقديم طلب جديد',
        location: 'الموقع:',
        costImpact: 'التكلفة التقديرية للأمر:',
        notes: 'ملاحظات الاستشاري / الإدارة:',
        approveBtn: 'اعتماد الطلب',
        rejectBtn: 'رفض',
        createModalTitle: 'تقديم طلب موقع / اعتماد هندسي',
        typeLabel: 'نوع الطلب',
        numberLabel: 'رقم الطلب (اختياري)',
        numberPlaceholder: 'تلقائي: WIR-002',
        reqTitle: 'عنوان الطلب',
        descLabel: 'تفاصيل ومحتوى الطلب',
        descPlaceholder: 'اشرح تفاصيل الاستلام أو الاستفسار الهندسي بالتفصيل...',
        locationLabel: 'موقع العمل في المشروع',
        costImpactLabel: 'التكلفة المالية التقديرية (SAR)',
        submitting: 'جاري التقديم...',
        submit: 'إرسال الطلب للاعتماد',
        reviewApproveTitle: 'اعتماد الطلب الهندسي',
        reviewRejectTitle: 'رفض الطلب الهندسي',
        reviewTargetLabel: 'الطلب:',
        reviewNotes: 'ملاحظات وقرار الاستشاري / الإدارة',
        reviewNotesPlaceholder: 'أدخل الملاحظات والتوجيهات الهندسية...',
        confirmApprove: 'تأكيد الاعتماد',
        confirmReject: 'تأكيد الرفض',
        processing: 'جاري المعالجة...',
        submitFailed: 'فشل تقديم الطلب الهندسي',
        updateFailed: 'فشل تحديث حالة الطلب الهندسي',
      },
      claims: {
        title: 'المستخلصات المالية للمشروع (Payment Claims)',
        subtitle: 'تتبع المستخلصات الدورية وضريبة القيمة المضافة 15%',
        newBtn: 'رفع مستخلص جديد',
        claimNumber: 'رقم المستخلص',
        period: 'الفترة',
        claimedAmount: 'المبلغ المطالب به',
        vatAmount: 'ضريبة القيمة المضافة (15%)',
        totalWithVat: 'الإجمالي شاملاً الضريبة',
        approvedAmount: 'المبلغ المعتمد',
        status: 'الحالة',
        certifyBtn: 'اعتماد للصرف',
        recordPaidBtn: 'تسجيل الصرف',
        to: 'إلى',
        createModalTitle: 'رفع مستخلص مالي جديد (Payment Claim)',
        periodStart: 'بداية الفترة',
        periodEnd: 'نهاية الفترة',
        amountExVat: 'المبلغ المطلوب (بدون ضريبة)',
        totalClaimWithVat: 'الإجمالي شاملاً الضريبة:',
        submitting: 'جاري التسجيل...',
        submit: 'حفظ المستخلص',
        submitFailed: 'فشل تقديم المستخلص المالي',
        updateFailed: 'فشل تحديث حالة المستخلص المالي',
      },
    },
  },

  en: {
    common: {
      appName: 'FieldOps KSA',
      appTagline: 'Construction Field Operations Platform',
      tenantFallback: 'Contracting Platform',
      logout: 'Sign Out',
      switchLanguage: 'العربية',
      currentLangLabel: 'English',
      cancel: 'Cancel',
      save: 'Save',
      saving: 'Saving...',
      close: 'Close',
      search: 'Search...',
      all: 'All',
      loading: 'Loading...',
      actions: 'Action',
      details: 'Details',
      sar: 'SAR',
      error: 'Error',
      viewFile: 'View File',
      delete: 'Delete',
      edit: 'Edit',
      saveFailed: 'Save failed',
      success: 'Success',
      footer: 'FieldOps KSA Platform • Real-time field documentation & weighted progress tracking',
    },
    roles: {
      owner: 'General Manager / Owner',
      pm: 'Project Manager',
      site_engineer: 'Site Engineer',
      viewer: 'Consultant / Client',
    },
    disciplines: {
      all: 'All Disciplines',
      architectural: 'Architectural',
      structural: 'Structural',
      mechanical: 'Mechanical',
      electrical: 'Electrical',
      contracts: 'Contracts',
      permits: 'Permits',
    },
    requestTypes: {
      WIR: 'Work Inspection Request (WIR)',
      RFI: 'Request for Information (RFI)',
      VARIATION_ORDER: 'Variation Order (VO)',
    },
    statuses: {
      active: 'Active On-Site',
      pending: 'Pending',
      approved: 'Approved',
      rejected: 'Rejected',
      under_review: 'Under Review',
      completed: 'Completed',
      submitted: 'Submitted',
      certified: 'Certified for Payment',
      paid_partially: 'Partially Paid',
      paid: 'Fully Paid',
    },
    login: {
      title: 'FieldOps KSA',
      subtitle: 'Field operations & project documentation platform for contractors',
      email: 'Email Address',
      emailPlaceholder: 'name@company.sa',
      password: 'Password',
      passwordPlaceholder: '••••••••',
      submit: 'Sign In to Dashboard',
      submitting: 'Signing in...',
      quickAccess: 'Or select a demo profile for instant access:',
      ownerTitle: 'General Manager',
      ownerSubtitle: 'Full Executive Authority',
      pmTitle: 'Project Manager',
      pmSubtitle: 'Operations & BOQ Control',
      engineerTitle: 'Site Engineer',
      engineerSubtitle: 'Field Logs & Inspections',
      consultantTitle: 'Owner Consultant',
      consultantSubtitle: 'Quality & Design Approvals',
      failed: 'Sign in failed. Please verify your credentials.',
    },
    projects: {
      activeProjects: 'Active Projects',
      inProgress: 'In Progress',
      avgProgress: 'Avg Weighted Progress',
      onSite: 'On-Site',
      totalContractValue: 'Total Contract Value',
      dailyReportsCount: 'Logged Daily Reports',
      reportsUnit: 'reports',
      searchPlaceholder: 'Search by project name, code, or city...',
      newProject: 'New Project',
      loading: 'Loading field projects data...',
      emptyTitle: 'No Projects Found',
      emptyDesc: 'Create a new project to start managing daily logs and site operations.',
      client: 'Client:',
      city: 'City:',
      contractValue: 'Contract Value:',
      consultant: 'Consultant:',
      notSpecified: 'Not Specified',
      weightedProgressBoq: 'Weighted Progress (BOQ)',
      dailyReports: 'reports',
      drawings: 'drawings',
      requests: 'requests',
      viewOperations: 'View Project',
      modal: {
        title: 'Create New Field Project',
        code: 'Project Code',
        name: 'Project Name',
        clientName: 'Client / Owner Name',
        consultantName: 'Consultant Office',
        city: 'City',
        contractValue: 'Contract Value (SAR)',
        startDate: 'Start Date',
        expectedEndDate: 'Expected End Date',
        saving: 'Saving...',
        saveBtn: 'Save & Activate Project',
      },
      createFailed: 'Failed to create project',
    },
    detail: {
      backToProjects: 'Back to Projects',
      executivePulse: 'Executive Pulse',
      executivePulseBadge: 'Live Pulse',
      notFound: 'Project Not Found',
      backHome: 'Back to Home',
      loading: 'Loading field operations data...',
      activeSite: 'Active Site',
      client: 'Client:',
      consultant: 'Consultant:',
      city: 'City:',
      contractValue: 'Contract Value:',
      weightedProgressLabel: 'Weighted Progress',
      calculatedFromBoq: 'Calculated from BOQ',
      liveFieldUpdate: 'Live On-Site Sync',
      tabs: {
        reports: 'Daily Site Reports',
        boq: 'BOQ & Progress',
        drawings: 'Drawings & Vault',
        requests: 'Site Requests',
        claims: 'Payment Claims',
      },
      boq: {
        title: 'Bill of Quantities (BOQ)',
        subtitle: 'Calculates overall weighted progress automatically upon any item update',
        addItem: 'Add BOQ Item',
        code: 'Code',
        description: 'Description of Work',
        unit: 'Unit',
        quantity: 'Quantity',
        unitPrice: 'Unit Price',
        total: 'Total (SAR)',
        weight: 'Weight',
        fieldProgress: 'Field Progress',
        modalTitle: 'Add New BOQ Item',
        itemCode: 'Item Code',
        descriptionOfWork: 'Description of Works',
        descPlaceholder: 'Detailed specification of item and scope...',
        totalQuantity: 'Total Quantity',
        unitPriceLabel: 'Unit Price (SAR)',
        weightPercentage: 'Relative Weight (%)',
        totalEstimated: 'Total Estimated Value:',
        saveItem: 'Save to BOQ',
        adding: 'Adding...',
        units: {
          m2: 'm²',
          m3: 'm³',
          lm: 'Linear Meter',
          ton: 'Ton',
          qty: 'Unit / Pcs',
          lumpsum: 'Lump Sum',
          hour: 'Hour',
        },
        progressFailed: 'Failed to update progress',
        addFailed: 'Failed to add BOQ item',
      },
      reports: {
        quickReport: 'New Daily Site Report',
        captureMedia: 'Upload Site Photos',
        visualStreamTitle: 'Field Visual Stream',
        visualStreamSubtitle: 'Latest real-time photo documentation from the site',
        sitePhoto: 'Site Photo',
        historyTitle: 'Historical Daily Reports',
        empty: 'No reports recorded yet. Submit today\'s report to get started!',
        approve: 'Approve Report',
        downloadPdf: 'Export PDF',
        shareWhatsApp: 'WhatsApp Share',
        manpower: 'Manpower:',
        workersUnit: 'workers & techs',
        weather: 'Weather:',
        weatherDefault: 'Moderate',
        engineer: 'Logged By:',
        workSummary: 'Daily Work Summary:',
        blockers: 'Site Blockers & Delays:',
        confirmApprove: 'Are you sure you want to officially approve this daily report?',
        approveFailed: 'Failed to approve report',
        saveFailed: 'Failed to save daily report',
        needReportFirst: 'Please submit a daily report first to attach photos.',
      },
      reportModal: {
        title: 'Log Daily Field Report',
        date: 'Report Date',
        manpower: 'On-Site Manpower Count',
        weather: 'Weather & Temperature',
        summary: 'Daily Work Accomplishments',
        summaryPlaceholder: 'Describe completed tasks, poured foundations, inspections...',
        blockers: 'Site Blockers & Remarks (Optional)',
        blockersPlaceholder: 'Any material delays, access limitations, weather halts...',
        submit: 'Submit Daily Report',
        submitting: 'Saving...',
      },
      mediaModal: {
        title: 'Upload Site Photo',
        selectReport: 'Select Linked Daily Report',
        reportPrefix: 'Report of:',
        file: 'Photo File / Camera Capture',
        caption: 'Photo Caption',
        captionPlaceholder: 'e.g. Reinforced concrete column casting',
        gpsCaptured: 'GPS coordinates recorded:',
        uploading: 'Uploading...',
        submit: 'Upload & Archive Photo',
        uploadFailed: 'Failed to upload site photo',
      },
      pulse: {
        title: 'Executive Project Pulse',
        actualProgress: 'Actual Progress:',
        latestManpower: 'Today / Recent Manpower:',
        workers: 'personnel',
        totalClaimed: 'Total Claims Submitted:',
        approvedClaims: 'Approved Claims:',
        latestReport: 'Latest Daily Report',
        failed: 'Failed to load executive summary',
      },
      drawings: {
        title: 'Engineering Drawings & Revision Vault',
        subtitle: 'Issued For Construction (IFC) drawings and version history',
        uploadBtn: 'Upload New Drawing',
        code: 'Code',
        titleCol: 'Drawing Title',
        discipline: 'Discipline',
        revision: 'Revision',
        status: 'IFC Status',
        user: 'Uploaded By',
        ifcApproved: 'Approved for Construction (IFC)',
        pendingApproval: 'Under Review',
        uploadModalTitle: 'Upload Engineering Drawing / Document',
        docTitle: 'Drawing / Document Title',
        docCode: 'Document Code',
        disciplineLabel: 'Discipline',
        revisionLabel: 'Revision (e.g. Rev 01)',
        ifcCheckbox: 'Issued For Construction (IFC) Approved',
        ifcNotice: '(Requires Project Manager or Owner permission to certify)',
        fileLabel: 'Drawing File (PDF, DWG, DXF, etc.)',
        uploading: 'Uploading...',
        submit: 'Upload & Archive Drawing',
        selectFile: 'Please select a drawing file to upload',
        uploadFailed: 'Failed to upload engineering drawing',
      },
      requests: {
        title: 'Site Submittals & Requests (WIR / RFI / VO)',
        subtitle: 'Work inspections, technical queries, and variation orders',
        submitBtn: 'Submit New Request',
        location: 'Location:',
        costImpact: 'Estimated Cost Impact:',
        notes: 'Consultant / Management Feedback:',
        approveBtn: 'Approve Request',
        rejectBtn: 'Reject',
        createModalTitle: 'Submit Site Request / Submittal',
        typeLabel: 'Request Type',
        numberLabel: 'Request Number (Optional)',
        numberPlaceholder: 'Auto: WIR-002',
        reqTitle: 'Request Title',
        descLabel: 'Scope & Details',
        descPlaceholder: 'Provide comprehensive technical details...',
        locationLabel: 'Location within Site',
        costImpactLabel: 'Estimated Financial Impact (SAR)',
        submitting: 'Submitting...',
        submit: 'Submit Request',
        reviewApproveTitle: 'Approve Site Request',
        reviewRejectTitle: 'Reject Site Request',
        reviewTargetLabel: 'Request:',
        reviewNotes: 'Consultant / Management Review Remarks',
        reviewNotesPlaceholder: 'Enter review conditions, instructions or rationale...',
        confirmApprove: 'Confirm Approval',
        confirmReject: 'Confirm Rejection',
        processing: 'Processing...',
        submitFailed: 'Failed to submit site request',
        updateFailed: 'Failed to update request status',
      },
      claims: {
        title: 'Payment Claims & Valuations',
        subtitle: 'Track periodic valuations, payment certificates, and 15% VAT',
        newBtn: 'New Payment Claim',
        claimNumber: 'Claim Number',
        period: 'Period',
        claimedAmount: 'Claimed Amount',
        vatAmount: 'VAT (15%)',
        totalWithVat: 'Total Incl. VAT',
        approvedAmount: 'Approved Amount',
        status: 'Status',
        certifyBtn: 'Certify for Payment',
        recordPaidBtn: 'Record Payment',
        to: 'to',
        createModalTitle: 'Create New Payment Claim',
        periodStart: 'Period Start',
        periodEnd: 'Period End',
        amountExVat: 'Claim Amount (Excl. VAT)',
        totalClaimWithVat: 'Total Claimed (Incl. VAT):',
        submitting: 'Submitting...',
        submit: 'Save Payment Claim',
        submitFailed: 'Failed to submit payment claim',
        updateFailed: 'Failed to update claim status',
      },
    },
  },
};

export const useLocaleStore = defineStore('locale', {
  state: () => {
    const saved = localStorage.getItem('fieldops_locale');
    const initialLocale = saved === 'en' ? 'en' : 'ar';
    // Sync document attributes immediately upon store instantiation
    if (typeof document !== 'undefined') {
      document.documentElement.setAttribute('lang', initialLocale);
      document.documentElement.setAttribute('dir', initialLocale === 'ar' ? 'rtl' : 'ltr');
    }
    return {
      locale: initialLocale,
    };
  },

  getters: {
    isRtl: (state) => state.locale === 'ar',
    currentLocale: (state) => state.locale,
  },

  actions: {
    setLocale(newLocale) {
      if (newLocale !== 'ar' && newLocale !== 'en') return;
      this.locale = newLocale;
      localStorage.setItem('fieldops_locale', newLocale);
      if (typeof document !== 'undefined') {
        document.documentElement.setAttribute('lang', newLocale);
        document.documentElement.setAttribute('dir', newLocale === 'ar' ? 'rtl' : 'ltr');
      }
    },

    toggleLocale() {
      const next = this.locale === 'ar' ? 'en' : 'ar';
      this.setLocale(next);
    },

    t(path, params = {}) {
      const parts = path.split('.');
      let current = messages[this.locale];
      for (const part of parts) {
        if (current && typeof current === 'object' && part in current) {
          current = current[part];
        } else {
          // fallback to Arabic if missing in English
          let fallback = messages['ar'];
          for (const fbPart of parts) {
            if (fallback && typeof fallback === 'object' && fbPart in fallback) {
              fallback = fallback[fbPart];
            } else {
              return path;
            }
          }
          current = fallback;
          break;
        }
      }

      if (typeof current === 'string') {
        return current.replace(/\{(\w+)\}/g, (_, key) => (params[key] !== undefined ? params[key] : `{${key}}`));
      }
      return typeof current === 'string' ? current : path;
    },

    formatCurrency(amount) {
      const val = parseFloat(amount) || 0;
      const hasDecimals = val % 1 !== 0;
      const opts = {
        minimumFractionDigits: hasDecimals ? 2 : 0,
        maximumFractionDigits: 2,
      };
      if (this.locale === 'ar') {
        return `${new Intl.NumberFormat('ar-SA', opts).format(val)} ر.س`;
      }
      return `SAR ${new Intl.NumberFormat('en-US', opts).format(val)}`;
    },

    formatNumber(num) {
      const val = parseFloat(num) || 0;
      return new Intl.NumberFormat(this.locale === 'ar' ? 'ar-SA' : 'en-US').format(val);
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString(this.locale === 'ar' ? 'ar-SA-u-ca-gregory' : 'en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
        });
      } catch (e) {
        return dateStr;
      }
    },

    formatTime(dateStr) {
      if (!dateStr) return '';
      try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return '';
        return d.toLocaleTimeString(this.locale === 'ar' ? 'ar-SA' : 'en-US', {
          hour: '2-digit',
          minute: '2-digit',
        });
      } catch (e) {
        return '';
      }
    },

    translateRole(role) {
      return messages[this.locale]?.roles?.[role] || messages['ar']?.roles?.[role] || role;
    },

    translateDiscipline(disc) {
      return messages[this.locale]?.disciplines?.[disc] || messages['ar']?.disciplines?.[disc] || disc;
    },

    translateRequestType(type) {
      return messages[this.locale]?.requestTypes?.[type] || messages['ar']?.requestTypes?.[type] || type;
    },

    translateStatus(status) {
      return messages[this.locale]?.statuses?.[status] || messages['ar']?.statuses?.[status] || status;
    },

    translateClaimStatus(status) {
      return messages[this.locale]?.statuses?.[status] || messages['ar']?.statuses?.[status] || status;
    },
  },
});
