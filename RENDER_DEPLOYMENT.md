# 🚀 Render पर GenZ Auth Deploy करने की Complete Guide

## 📋 Overview (संक्षिप्त जानकारी)
यह guide आपको step-by-step बताएगी कि कैसे अपनी GenZ Auth PHP application को Render पर Docker के साथ deploy करें।

---

## ✅ Prerequisites (जरूरी चीज़ें)
1. ✓ GitHub account
2. ✓ Render account (free signup: render.com)
3. ✓ Database credentials (अपना MySQL/MariaDB database)

---

## 📂 Step 1: Code को GitHub पर Push करें

अगर आपका code already GitHub पर नहीं है, तो:

```bash
# Terminal में ये commands run करें
git init
git add .
git commit -m "Initial commit for Render deployment"
git branch -M main
git remote add origin <your-github-repo-url>
git push -u origin main
```

अगर पहले से GitHub पर है, तो बस latest changes push करें:

```bash
git add .
git commit -m "Ready for Render deployment with Docker"
git push origin main
```

---

## 🌐 Step 2: Render पर Web Service बनाएं

1. **[render.com](https://render.com)** पर जाएं और login करें
2. Dashboard पर **"New +"** button पर click करें
3. **"Web Service"** select करें
4. **"Connect GitHub"** पर click करके अपना GitHub account connect करें
5. अपनी **GenZ Auth repository** को select करें

---

## ⚙️ Step 3: Service Settings Configure करें

### Basic Settings (बुनियादी सेटिंग्स):
- **Name**: `genzauth` (या कोई भी नाम जो आपको पसंद हो)
- **Region**: अपने location के सबसे पास वाला region select करें (India के लिए Singapore)
- **Branch**: `main` (या आपकी default branch)
- **Runtime**: **Docker** ⚠️ (यह बहुत जरूरी है! PHP option नहीं है इसलिए Docker चुनें)
- **Instance Type**: **Free** (शुरुआत के लिए) या **Starter** ($7/month for better performance)

### Build & Deploy:
✅ Render automatically आपकी `Dockerfile` को detect कर लेगा - कोई changes की जरूरत नहीं!

---

## 🔐 Step 4: Environment Variables Setup (बहुत जरूरी!)

**"Advanced"** section को expand करें → **"Add Environment Variable"** पर click करें

### 📊 Database Configuration (Required - जरूर add करें):

**ध्यान दें**: अपनी actual database details यहां डालें!

| Key | Value | Secret? |
|-----|-------|---------|
| `DATABASE_HOST` | आपका database host (जैसे: us9.endercloud.in) | ✓ |
| `DATABASE_USERNAME` | आपका database username | ✓ |
| `DATABASE_PASSWORD` | आपका database password | ✓ |
| `DATABASE_NAME` | आपका database name | ✓ |
| `DATABASE_PORT` | आपका database port (जैसे: 6555 या 3306) | - |

### 🎯 Optional Features (जरूरत हो तो add करें):

| Key | Value | कब चाहिए? |
|-----|-------|-----------|
| `GOOGLE_CLIENT_ID` | Your Google OAuth Client ID | Google login के लिए |
| `GOOGLE_CLIENT_SECRET` | Your Google OAuth Secret | Google login के लिए |
| `LOG_WEBHOOK` | Discord Webhook URL | Login logs के लिए |
| `ADMIN_WEBHOOK` | Discord Webhook URL | Admin actions log के लिए |
| `AWS_ACCESS_KEY` | AWS Access Key | Email sending के लिए |
| `AWS_SECRET_KEY` | AWS Secret Key | Email sending के लिए |
| `REDIS_PASSWORD` | Redis Password | External Redis के लिए (built-in है तो छोड़ दें) |

### 💡 Tips:
- हर environment variable के आगे **lock icon (🔒)** पर click करके "Secret" mark करें
- `.env.example` file में sample values देख सकते हैं
- Password और sensitive data हमेशा secret mark करें!

---

## 🚀 Step 5: Deploy करें!

1. सभी settings check करने के बाद **"Create Web Service"** पर click करें
2. Render अब automatically ये सब करेगा:
   - ✓ आपकी repository clone करेगा
   - ✓ Docker image build करेगा (5-10 मिनट लगेंगे)
   - ✓ Application deploy करेगा
3. Build logs में deployment progress देखें

### 📊 Deployment Logs में आपको दिखेगा:
```
==> Cloning from https://github.com/...
==> Building...
==> Building Docker image...
==> Successfully built image
==> Starting service...
==> Service is live at https://your-app.onrender.com
```

### ⏱️ समय:
- **First deployment**: 5-10 मिनट (image build होती है)
- **Future deployments**: 2-5 मिनट (cache use होता है)

---

## 🌍 Step 6: अपनी Site Access करें

Deployment complete होने पर, Render आपको एक URL देगा:
```
https://your-app-name.onrender.com
```

### ✅ Testing:
1. इस URL को browser में open करें
2. अपने GenZ Auth dashboard को check करें
3. Login/Register test करें
4. सभी pages properly काम कर रहे हैं confirm करें

**🎉 बधाई हो! आपकी site अब live है!**

---

## 📌 Important Notes (जरूरी बातें)

### 🆓 Free Tier की Limitations:
- ⏰ **15 minutes inactivity** के बाद service sleep mode में चली जाती है
- 🐌 **Cold start**: पहली request के लिए 30-60 seconds लग सकते हैं
- ⏳ **750 hours/month** free (एक service 24/7 के लिए काफी है)
- 🚫 **Logs retention**: सिर्फ 7 days

### ⚡ Always Running रखने के लिए:
- 💰 **Starter Plan** में upgrade करें ($7/month)
- ✅ **Advantages**:
  - कभी spin down नहीं होगी
  - Instant response times
  - Custom domains support
  - More RAM & CPU
  - Better uptime guarantee

### 🗄️ Database Connection:
- आपका external database बिल्कुल सही काम करेगा
- Database port Render servers से accessible होना चाहिए
- Deployment के बाद connection test जरूर करें
- अगर connection error आए तो database firewall settings check करें

### 🔄 Redis & Process Management:
- Docker में **local Redis** automatically run होगी
- **Supervisord** सभी services (Redis, PHP-FPM, Nginx) को manage करेगा
- अगर कोई service crash हो तो automatically restart होगी
- External Redis की जरूरत नहीं (unless आप चाहें)
- Cache properly काम करेगा

### 📁 Docker Setup Files:
आपके repository में ये files already हैं:
- ✓ `Dockerfile` - Container image configuration
- ✓ `nginx.conf` - Web server configuration  
- ✓ `supervisord.conf` - Process management
- ✓ `start.sh` - Startup script
- ✓ `.env.example` - Environment variables reference

---

## 🔧 Troubleshooting (समस्या समाधान)

### ❌ Build Fail हो रही है:
**Check करें**:
1. ✓ Docker logs को Render dashboard में देखें
2. ✓ सभी files (Dockerfile, nginx.conf, start.sh) repository में हैं
3. ✓ start.sh को execute permission है
4. ✓ Dockerfile में कोई syntax error तो नहीं

**Solution**:
```bash
# Local test करें
docker build -t genzauth .
```

### 🌐 Site Load नहीं हो रही:
**Check करें**:
1. ✓ Environment Variables सही set हैं (Dashboard → Environment)
2. ✓ Runtime Logs check करें errors के लिए
3. ✓ Database credentials verify करें
4. ✓ Service running है (Dashboard में green status)

**Common Errors**:
- `Database configuration error` → Environment variables missing हैं
- `503 Service Unavailable` → Service starting हो रही है, wait करें
- `Connection refused` → Database accessible नहीं है

### 🗄️ Database Connection Error:
**Fix Steps**:
1. ✓ DATABASE_HOST, USERNAME, PASSWORD double-check करें
2. ✓ Database firewall में Render IPs को allow करें
3. ✓ Port number सही है confirm करें
4. ✓ Database service running है verify करें

**Test करने के लिए**:
```bash
# Render Shell में (Dashboard → Shell tab)
mysql -h DATABASE_HOST -u USERNAME -p -P PORT
```

### 📄 Pages काम नहीं कर रहे:
**Check करें**:
1. ✓ nginx.conf properly configured है
2. ✓ PHP files की permissions correct हैं
3. ✓ Browser console में errors देखें (F12)
4. ✓ Logs में PHP errors check करें

**Fix**:
- Cache clear करें: `Ctrl+Shift+R` (Windows) या `Cmd+Shift+R` (Mac)
- Service restart करें: Dashboard → Manual Deploy → "Clear build cache & deploy"

---

## 🌐 Custom Domain Setup (Optional)

अगर आप अपना domain use करना चाहते हैं (जैसे: auth.yourdomain.com):

### Steps:
1. **Render Dashboard** में अपनी service की Settings में जाएं
2. **"Custom Domains"** section में **"Add Custom Domain"** पर click करें
3. अपना domain या subdomain enter करें (जैसे: `auth.yourdomain.com`)
4. Render आपको **DNS records** दिखाएगा:
   ```
   Type: CNAME
   Name: auth (or @)
   Value: your-app.onrender.com
   ```
5. अपने **Domain Provider** (GoDaddy/Namecheap/Cloudflare etc) में जाकर ये DNS record add करें
6. **Save करें** और 5-30 minutes wait करें (DNS propagation)

### 🔒 SSL Certificate:
- ✅ **Automatic & Free** SSL certificate मिलेगा
- 🔄 **Auto-renewal** होगा, आपको कुछ करने की जरूरत नहीं
- 🌍 **HTTPS** automatically enable हो जाएगा

### ✅ Verification:
```bash
# DNS check करने के लिए
nslookup auth.yourdomain.com
# या
dig auth.yourdomain.com
```

---

## 🔄 App को Update करना

कोई भी code changes करने के बाद:

### Method 1: Automatic Deployment (Recommended)
```bash
# Changes commit करें
git add .
git commit -m "Updated feature XYZ"
git push origin main
```

✅ **Render automatically** rebuild और redeploy कर देगा! (2-5 मिनट)

### Method 2: Manual Deployment
1. Render Dashboard में जाएं
2. अपनी service select करें
3. **"Manual Deploy"** → **"Deploy latest commit"** पर click करें
4. Wait for deployment to complete

### 🔄 Rollback (पुराने version पर जाना):
अगर नया update काम नहीं कर रहा:
1. Dashboard → **"Events"** tab
2. Working deployment select करें
3. **"Redeploy"** click करें

### 📊 Deployment Status देखें:
- **Building** 🟡 → Image build हो रही है
- **Deploying** 🟠 → Service deploy हो रही है
- **Live** 🟢 → Successfully deployed!
- **Failed** 🔴 → Logs check करें

---

## 💬 Support & Help

### अगर कोई problem आए:

1. **Render Logs Check करें**:
   - Dashboard → आपकी Service → **"Logs"** tab
   - Errors को carefully पढ़ें

2. **Documentation पढ़ें**:
   - Render Docs: [render.com/docs](https://render.com/docs)
   - Docker Guide: [render.com/docs/docker](https://render.com/docs/docker)

3. **Community से Help लें**:
   - Render Community: [community.render.com](https://community.render.com)
   - GitHub Issues में search करें

4. **Common Resources**:
   - [Environment Variables Guide](https://render.com/docs/environment-variables)
   - [Custom Domains](https://render.com/docs/custom-domains)
   - [Troubleshooting](https://render.com/docs/troubleshooting-deploys)

### 📞 Direct Support:
- Render Support: support@render.com (Paid plans only)
- Community Forum: सबसे fast response
- Twitter: @render (status updates)

---

## 📝 Quick Checklist

Deployment से पहले confirm करें:

- [ ] ✅ Code GitHub पर push है
- [ ] ✅ Dockerfile, nginx.conf, start.sh files हैं
- [ ] ✅ Database credentials ready हैं
- [ ] ✅ Render account बना लिया है
- [ ] ✅ Runtime "Docker" select किया
- [ ] ✅ सभी environment variables set किए
- [ ] ✅ Region select किया (Singapore for India)

Deployment के बाद test करें:

- [ ] ✅ Homepage load हो रहा है
- [ ] ✅ Login/Register काम कर रहा है
- [ ] ✅ Database connection working है
- [ ] ✅ सभी pages properly load हो रहे हैं
- [ ] ✅ API endpoints responding हैं

---

## 🎉 Congratulations!

**आपका GenZ Auth अब professionally Render पर host है!** ✅

### Next Steps:
1. 🔐 **Security**: Strong passwords use करें
2. 📊 **Monitoring**: Regular logs check करते रहें
3. 🔄 **Backups**: Database का regular backup लें
4. 📈 **Performance**: Logs से performance monitor करें
5. 💰 **Upgrade**: Traffic बढ़ने पर Starter plan consider करें

**Happy Deploying! 🚀**
