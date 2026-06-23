<div align="center">
  <h1>🚨 CoA (Crisis Containment Service)</h1>
  <p><i>Web platform for authorities to manage and broadcast emergency situations.</i></p>
  
  <p>
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
    <img src="https://img.shields.io/badge/PostgreSQL-316192?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL" />
    <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
    <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5" />
    <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3" />
  </p>
</div>

---

## 📖 Overview

**CoA (Crisis Containment Service)** is a centralized web platform designed for authorities to efficiently manage emergency situations such as earthquakes, fires, and floods. The system ingests public disaster data, tracks affected zones, and automates public safety broadcasts.

<br>

## 🎥 Project Demonstration

<div align="center">
  <a href="https://youtu.be/d_OphiTu9vk" target="_blank">
    <img src="https://img.youtube.com/vi/d_OphiTu9vk/maxresdefault.jpg" alt="CoA Demonstration Video" width="800" />
  </a>
  <br>
  <p><i>Click the image above to watch the full feature demonstration on YouTube.</i></p>
</div>

<br>

---

## ✨ Core Features

* 🌍 **Real-Time Disaster Tracking:** Consumes public earthquake data to update disaster events dynamically via background sync services.
* 📡 **CAP Integration:** Utilizes the **Common Alerting Protocol (CAP)** to format and transmit standardized notifications and alerts to affected populations.
* 🗺️ **Dynamic Evacuation Routing:** Automatically calculates and provides information on available shelters and optimal rescue routes based on the specific category of the recorded calamity.
* 🛡️ **Authority Dashboard:** Secure management interface for officials to monitor events, update statuses, and trigger broadcasts.

<br>

---

## 🏗️ Technical Architecture

The project is built with a structured backend and is fully containerized for consistent deployment.

* ⚙️ **Backend:** Custom PHP architecture with strict separation of concerns (`/src`, `/config`, `/database`).
* 🗄️ **Database:** **PostgreSQL** for robust, relational storage of dynamic disaster data and authority credentials.
* 🎨 **Templating:** Server-side rendered views using dedicated templates (`/templates/pages`).
* 🐳 **Containerization:** Fully dockerized environment utilizing both a `Dockerfile` and `docker-compose.yml` for rapid orchestration.
* 🔄 **Automated Syncing:** Dedicated scripts (`run_sync.php`) handle the periodic fetching and database synchronization of external earthquake APIs.

<br>

---

## 🚀 Getting Started

### Prerequisites
Make sure you have the following installed:
* [Docker](https://www.docker.com/get-started)
* [Docker Compose](https://docs.docker.com/compose/install/)

### Installation & Execution

**1. Clone the repository:**
```bash
git clone [https://github.com/Stupu-Eduard/webb.git](https://github.com/Stupu-Eduard/webb.git)
cd webb
```

**2. Build and start the containers:**
```bash
docker-compose up -d --build
```

**3. Run the initial database sync:**
*(This populates the disaster data into your PostgreSQL database)*
```bash
php run_sync.php
```

---

<h3>🤝 Contributors</h3>
<table align="center">
  <tr>
    <td align="center">
      <a href="https://github.com/Stupu-Eduard">
        <img src="https://avatars.githubusercontent.com/Stupu-Eduard" width="100" style="border-radius: 50%;" alt="Stupu Eduard"/><br />
        <sub><b>Stupu Eduard</b></sub>
      </a><br />
      <i>Backend & Infrastructure</i>
    </td>
    <td align="center">
      <a href="https://github.com/razx11111">
        <img src="https://avatars.githubusercontent.com/razx11111" width="100" style="border-radius: 50%;" alt="Gheoca Razvan"/><br />
        <sub><b>Gheoca Răzvan</b></sub>
      </a><br />
      <i>Backend & Infrastructure</i>
    </td>
  </tr>
</table>
