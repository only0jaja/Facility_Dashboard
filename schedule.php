<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "your_database_name";

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch schedule data
$sql = "SELECT Code, Description, day, start_time, end_time, Room_code, F_name, L_name FROM schedule JOIN subject ON schedule.subject_id = subject.id JOIN user ON schedule.faculty_id = user.id JOIN classrooms ON schedule.Room_id = classrooms.Room_id WHERE section_id = '1' ORDER BY day, start_time";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/schedule.css">
</head>

<body>
    <div class="sidebar" id="sidebar">
        <h1>Dashboard</h1>
        <a href="#" class="active">🏠 Home</a>
        <a href="users.php">👥 Users</a>
        <a href="rooms.php">📁 Rooms</a>
        <a href="access_logs.php">📜 Access Logs</a>
        <a href="#">⚙️ Schedule</a>
        <a href="logout.php">🚪 Log out</a>
        <div class="user">
            👤 <span>Juan<br><small>Faculty Member</small></span>
        </div>
    </div>

    <div class="schedule">
        <table>
            <thead>
                <tr>
                   <th>COURSE DESCRIPTION</th>
                   <th>SECTION</th>
                   <th>DAY</th>
                   <th>STARTTIME</th>
                   <th>END TIME</th>
                   <th>ROOM</th>
                   <th>FACULTY</th>
                </tr>
            </thead>
            <tbody>
              <tr>
                <td>Readings in Philippine History</td>
                <td>BSA 1-11</td>
                <td>M</td>
                <td>9:00AM</td>
                <td>12:00PM</td>
                <td>301</td>
                <td>VDGUTIERREZ</td>
              </tr>
              <tr>
                <td>Financial Accounting and Reporting</td>
                <td>BSA 1-11</td>
                <td>MS</td>
                <td>4:00PM</td>
                <td>7:00PM</td>
                <td>E106</td>
                <td>GEMISLANG</td>
              </tr>
              <tr>
                <td>ACC101</td>
                <td>Managerial Economics</td>
                <td>3</td>
                <td>BSA 1-11</td>
                <td>S</td>
                <td>1:00PM - 4:00PM</td>
                <td>E106</td>
                <td>ccaqbzbp</td>
                <td>MEDDELAPENA</td>
              </tr>
              <tr>
                <td>NSTP11</td>
                <td>National Service Training Program 1</td>
                <td>3</td>
                <td>BSA 1-11</td>
                <td>S</td>
                <td>9:00AM - 12:00PM</td>
                <td>301</td>
                <td>2zwkfbdi</td>
                <td>RSARIOLA</td>
              </tr>
              <tr>
                <td>GEE101</td>
                <td>GE Elective 1- The Entrepreneurial Mind</td>
                <td>3</td>
                <td>BSA 1-11</td>
                <td>TH</td>
                <td>9:00AM - 12:00PM</td>
                <td>302</td>
                <td>4iwtzy23</td>
                <td>MNATO</td>
              </tr>
              <tr>
                <td>GE101</td>
                <td>Understanding the Self</td>
                <td>3</td>
                <td>BSA 1-11</td>
                <td>W</td>
                <td>1:00PM - 4:00PM</td>
                <td>301</td>
                <td>2fee57wk</td>
                <td>AAMAGLASANG</td>
              </tr>
              <tr>
                <td>PED101</td>
                <td>Physical Education 1</td>
                <td>2</td>
                <td>BSA 1-11</td>
                <td>W</td>
                <td>8:00AM - 10:00AM</td>
                <td>GYM</td>
                <td>w5odz6ev</td>
                <td>AALLUMBERIO</td>
              </tr>
            </tbody>
        </table>
<div class="table2">
        <table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>OAC415</td>
      <td>Internet Research for Business (w/ laboratory)</td>
      <td>3</td>
      <td>BSOA 1-41</td>
      <td>TH</td>
      <td>10:00AM - 1:00PM</td>
      <td>301</td>
      <td>56bp44wa</td>
      <td>JLSANTIAGO</td>
    </tr>
    <tr>
      <td>OAE404</td>
      <td>Web Design (w/ laboratory)</td>
      <td>3</td>
      <td>BSOA 1-41</td>
      <td>TH</td>
      <td>1:00PM - 4:00PM</td>
      <td>LAB A</td>
      <td>se57cmos</td>
      <td>MLDELOSSANTOS</td>
    </tr>
    <tr>
      <td>OAE405</td>
      <td>Introduction to Project Management (w/ laboratory)</td>
      <td>3</td>
      <td>BSOA 1-41</td>
      <td>S</td>
      <td>9:00AM - 12:00PM</td>
      <td>E107</td>
      <td>5kkxa4lg</td>
      <td>MNATO</td>
    </tr>
  </tbody>
</table>

</div>

<div class="table3">
    <table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>GE101</td>
      <td>Understanding the Self</td>
      <td>3</td>
      <td>BSEN 1-11</td>
      <td>W</td>
      <td>1:00PM - 4:00PM</td>
      <td>301</td>
      <td>2fee57wk</td>
      <td>AAMAGLASANG</td>
    </tr>
    <tr>
      <td>GE102</td>
      <td>Readings in Philippine History</td>
      <td>3</td>
      <td>BSEN 1-11</td>
      <td>M</td>
      <td>9:00AM - 12:00PM</td>
      <td>301</td>
      <td>5icisusd</td>
      <td>VDGUTIERREZ</td>
    </tr>
    <tr>
      <td>ENC101</td>
      <td>Entrepreneurial Behavior</td>
      <td>3</td>
      <td>BSEN 1-11</td>
      <td>TH</td>
      <td>9:00AM - 12:00PM</td>
      <td>302</td>
      <td>4iwtzy23</td>
      <td>MNATO</td>
    </tr>
    <tr>
      <td>ACC102</td>
      <td>Financial Accounting &amp; Reporting</td>
      <td>6</td>
      <td>BSEN 1-11</td>
      <td>MS</td>
      <td>4:00PM - 7:00PM</td>
      <td>E106</td>
      <td>hca7kki6</td>
      <td>GEMISLANG</td>
    </tr>
    <tr>
      <td>NSTP11</td>
      <td>National Service Training Program 1</td>
      <td>3</td>
      <td>BSEN 1-11</td>
      <td>S</td>
      <td>9:00AM - 12:00PM</td>
      <td>301</td>
      <td>2zwkfbdi</td>
      <td>RSARIOLA</td>
    </tr>
    <tr>
      <td>PED101</td>
      <td>Physical Education 1</td>
      <td>2</td>
      <td>BSEN 1-11</td>
      <td>W</td>
      <td>8:00AM - 10:00AM</td>
      <td>GYM</td>
      <td>w5odz6ev</td>
      <td>AALLUMBERIO</td>
    </table>
</div>

<div class="table4">
    <table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>GE205</td>
      <td>Art Appreciation</td>
      <td>3</td>
      <td>BSEN 1-21</td>
      <td>M</td>
      <td>1:00PM - 4:00PM</td>
      <td>301</td>
      <td>s7rrqmyf</td>
      <td>AAMAGLASANG</td>
    </tr>
    <tr>
      <td>GE206</td>
      <td>Purposive Communication</td>
      <td>3</td>
      <td>BSEN 1-21</td>
      <td>W</td>
      <td>5:00PM - 8:00PM</td>
      <td>301</td>
      <td>z5acy5n4</td>
      <td>RHGUMBAN</td>
    </tr>
    <tr>
      <td>GEE101</td>
      <td>GE Elective 1 - Living in the IT Era</td>
      <td>3</td>
      <td>BSEN 1-21</td>
      <td>S</td>
      <td>1:00PM - 4:00PM</td>
      <td>305</td>
      <td>nyils6ih</td>
      <td>MLADENAVA</td>
    </tr>
    <tr>
      <td>ENC205</td>
      <td>Market Research and Consumer Behavior</td>
      <td>3</td>
      <td>BSEN 1-21</td>
      <td>M</td>
      <td>4:00PM - 7:00PM</td>
      <td>E107</td>
      <td>aqnxdocw</td>
      <td>JLSANTIAGO</td>
    </tr>
    <tr>
      <td>CBM201</td>
      <td>Production and Operations Management</td>
      <td>3</td>
      <td>BSEN 1-21</td>
      <td>S</td>
      <td>9:00AM - 12:00PM</td>
      <td>302</td>
      <td>h63zz4oa</td>
      <td>MEDDELAPENA</td>
    </tr>
    <tr>
      <td>PED203</td>
      <td>Physical Education 3</td>
      <td>2</td>
      <td>BSEN 1-21</td>
      <td>M</td>
      <td>10:00AM - 12:00PM</td>
      <td>GYM</td>
      <td>lffro3yp</td>
      <td>AALLUMBERIO</td>
    </tr>
  </tbody>
</table>
</div>

<div class="table5">
<table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>GE308</td>
      <td>Science, Technology, and Society</td>
      <td>3</td>
      <td>BSEN 1-31</td>
      <td>W</td>
      <td>4:00PM - 7:00PM</td>
      <td>302</td>
      <td>cpfisfz6</td>
      <td>SMPANDAO</td>
    </tr>
    <tr>
      <td>GEE303</td>
      <td>GE Elective 3- Business Logic</td>
      <td>3</td>
      <td>BSEN 1-31</td>
      <td>T</td>
      <td>1:00PM - 4:00PM</td>
      <td>302</td>
      <td>rq2yq4xi</td>
      <td>AAMAGLASANG</td>
    </tr>
    <tr>
      <td>ENC307</td>
      <td>Business Law and Taxation</td>
      <td>3</td>
      <td>BSEN 1-31</td>
      <td>F</td>
      <td>6:00PM - 9:00PM</td>
      <td>OL</td>
      <td>o33unaii</td>
      <td>JFMELCHOR</td>
    </tr>
    <tr>
      <td>ENC308</td>
      <td>Social Entrepreneurship</td>
      <td>3</td>
      <td>BSEN 1-31</td>
      <td>S</td>
      <td>4:00PM - 7:00PM</td>
      <td>E108</td>
      <td>psrqfpp3</td>
      <td>SDELEON</td>
    </tr>
    <tr>
      <td>ENE302</td>
      <td>Elective 2- Wholesale and Retail Sales Management</td>
      <td>3</td>
      <td>BSEN 1-31</td>
      <td>T</td>
      <td>5:00PM - 8:00PM</td>
      <td>E108</td>
      <td>c7wnhvdx</td>
      <td>RHGUMBAN</td>
    </tr>
    <tr>
      <td>ENS302</td>
      <td>Tourism Business 2</td>
      <td>3</td>
      <td>BSEN 1-31</td>
      <td>M</td>
      <td>5:00PM - 8:00PM</td>
      <td>E108</td>
      <td>hfoki4w4</td>
      <td>RHGUMBAN</td>
    </tr>
  </tbody>
</table>
</div>

<div class="table6">
<table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>ENC412</td>
      <td>Business Plan Implementation 1</td>
      <td>5</td>
      <td>BSEN 1-41</td>
      <td>TH</td>
      <td>4:00PM - 7:00PM</td>
      <td>E108</td>
      <td>oi2oidsy</td>
      <td>JLSANTIAGO</td>
    </tr>
    <tr>
      <td>ENC414</td>
      <td>Programs and Policies on Enterprise Development</td>
      <td>3</td>
      <td>BSEN 1-41</td>
      <td>S</td>
      <td>9:00AM - 12:00PM</td>
      <td>305</td>
      <td>pflls5x6</td>
      <td>SDELEON</td>
    </tr>
    <tr>
      <td>ENS404</td>
      <td>Tourism Business 4</td>
      <td>3</td>
      <td>BSEN 1-41</td>
      <td>TH</td>
      <td>5:00PM - 8:00PM</td>
      <td>CONFE</td>
      <td>sn6u3soy</td>
      <td>RHGUMBAN</td>
    </tr>
    <tr>
      <td>ENE404</td>
      <td>Elective 4- Managing a Service Enterprise</td>
      <td>3</td>
      <td>BSEN 1-41</td>
      <td>S</td>
      <td>4:00PM - 7:00PM</td>
      <td>304</td>
      <td>oepw3rrv</td>
      <td>MNATO</td>
    </tr>
    <tr>
      <td>ENC413</td>
      <td>Financial Management</td>
      <td>3</td>
      <td>BSEN 1-41</td>
      <td>S</td>
      <td>1:00PM - 4:00PM</td>
      <td>304</td>
      <td>iovdvo4w</td>
      <td>GEMISLANG</td>
    </tr>
  </tbody>
</table>
</div>
<div class="table7">
    <table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>GE101</td>
      <td>Understanding the Self</td>
      <td>3</td>
      <td>ACT 1-11</td>
      <td>F</td>
      <td>11:00PM - 2:00PM</td>
      <td>302</td>
      <td>hft5esmz</td>
      <td>AAMAGLASANG</td>
    </tr>
    <tr>
      <td>ITC101</td>
      <td>Introduction to Computing</td>
      <td>3</td>
      <td>ACT 1-11</td>
      <td>TH</td>
      <td>9:00AM - 12:00PM</td>
      <td>306</td>
      <td>3zwg3i4j</td>
      <td>MLDELOSSANTOS</td>
    </tr>
    <tr>
      <td>ITC102</td>
      <td>Computer Programming 1 (LECTURE) - Batch A</td>
      <td>3</td>
      <td>ACT 1-11</td>
      <td>S</td>
      <td>7:00AM - 9:00AM</td>
      <td>301</td>
      <td>ipll2syc</td>
      <td>FMTORINO</td>
    </tr>
    <tr>
      <td>ITC102</td>
      <td>Computer Programming 1 (LAB) - Batch A</td>
      <td>3</td>
      <td>ACT 1-11</td>
      <td>S</td>
      <td>9:00AM - 12:00PM</td>
      <td>LAB B</td>
      <td>ipll2syc</td>
      <td>FMTORINO</td>
    </tr>
    <tr>
      <td>ITC102</td>
      <td>Computer Programming 1 (LECTURE) - Batch B</td>
      <td>3</td>
      <td>ACT 1-11</td>
      <td>S</td>
      <td>1:00PM - 3:00PM</td>
      <td>301</td>
      <td>ipll2syc</td>
      <td>FMTORINO</td>
    </tr>
    <tr>
      <td>ITC102</td>
      <td>Computer Programming 1 (LAB) - Batch B</td>
      <td>3</td>
      <td>ACT 1-11</td>
      <td>S</td>
      <td>3:00PM - 6:00PM</td>
      <td>LAB B</td>
      <td>ipll2syc</td>
      <td>FMTORINO</td>
    </tr>
    <tr>
      <td>NSTP11</td>
      <td>Civic Welfare Training Service - NSTP1</td>
      <td>3</td>
      <td>ACT 1-11</td>
      <td>F</td>
      <td>7:00AM - 10:00AM</td>
      <td>301</td>
      <td>6uoruqtb</td>
      <td>NSTP1</td>
    </tr>
    <tr>
      <td>PED101</td>
      <td>Physical Education 1 ( Physical Fitness )</td>
      <td>2</td>
      <td>ACT 1-11</td>
      <td>W</td>
      <td>10:00AM - 12:00PM</td>
      <td>GYM</td>
      <td>idcoozv4</td>
      <td>AALLUMBERIO</td>
    </tr>
  </tbody>
</table>
</div>
<div class="table8">
    <table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>GE101</td>
      <td>Understanding the Self</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>F</td>
      <td>11:00PM - 2:00PM</td>
      <td>302</td>
      <td>hft5esmz</td>
      <td>AAMAGLASANG</td>
    </tr>
    <tr>
      <td>GE102</td>
      <td>Readings in Philippine History</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>T</td>
      <td>9:00AM - 12:00PM</td>
      <td>301</td>
      <td>7urx7yer</td>
      <td>VDGUTIERREZ</td>
    </tr>
    <tr>
      <td>GEE101</td>
      <td>GE Elective 1 - The Entrepreneurial Mind</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>F</td>
      <td>3:00PM - 6:00PM</td>
      <td>301</td>
      <td>z3ncs4nq</td>
      <td>MNATO</td>
    </tr>
    <tr>
      <td>ITC101</td>
      <td>Introduction to Computing</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>TH</td>
      <td>9:00AM - 12:00PM</td>
      <td>306</td>
      <td>3zwg3i4j</td>
      <td>MLDELOSSANTOS</td>
    </tr>
    <tr>
      <td>ITC102</td>
      <td>Computer Programming 1 (LECTURE) - Batch A</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>S</td>
      <td>7:00AM - 9:00AM</td>
      <td>301</td>
      <td>ipll2syc</td>
      <td>FMTORINO</td>
    </tr>
    <tr>
      <td>ITC102</td>
      <td>Computer Programming 1 (LAB) - Batch A</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>S</td>
      <td>9:00AM - 12:00PM</td>
      <td>LAB B</td>
      <td>ipll2syc</td>
      <td>FMTORINO</td>
    </tr>
    <tr>
      <td>ITC102</td>
      <td>Computer Programming 1 (LECTURE) - Batch B</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>S</td>
      <td>1:00PM - 3:00PM</td>
      <td>301</td>
      <td>ipll2syc</td>
      <td>FMTORINO</td>
    </tr>
    <tr>
      <td>ITC102</td>
      <td>Computer Programming 1 (LAB) - Batch B</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>S</td>
      <td>3:00PM - 6:00PM</td>
      <td>LAB B</td>
      <td>ipll2syc</td>
      <td>FMTORINO</td>
    </tr>
    <tr>
      <td>NSTP11</td>
      <td>NSTP1 - Civic Welfare Training Service</td>
      <td>3</td>
      <td>BSIT 1-11</td>
      <td>F</td>
      <td>7:00AM - 10:00AM</td>
      <td>301</td>
      <td>6uoruqtb</td>
      <td>NSTP1</td>
    </tr>
    <tr>
      <td>PED101</td>
      <td>Physical Education 1 ( Physical Fitness )</td>
      <td>2</td>
      <td>BSIT 1-11</td>
      <td>W</td>
      <td>10:00AM - 12:00PM</td>
      <td>GYM</td>
      <td>idcoozv4</td>
      <td>AALLUMBERIO</td>
    </tr>
  </tbody>
</table>
</div>
<div class="table9">
    <table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>GE105</td>
      <td>Art Appreciation</td>
      <td>3</td>
      <td>BSIT 1-21</td>
      <td>M</td>
      <td>1:00PM - 4:00PM</td>
      <td>301</td>
      <td>s7rrqmyf</td>
      <td>AAMAGLASANG</td>
    </tr>
    <tr>
      <td>PED203</td>
      <td>Physical Education 3 ( Individual and Dual Sports )</td>
      <td>2</td>
      <td>BSIT 1-21</td>
      <td>M</td>
      <td>8:00AM - 10:00AM</td>
      <td>GYM</td>
      <td>mtx5xkru</td>
      <td>AALLUMBERIO</td>
    </tr>
    <tr>
      <td>GEE203</td>
      <td>GE Elective 3 - Living in the IT Era</td>
      <td>3</td>
      <td>BSIT 1-21</td>
      <td>S</td>
      <td>1:00PM - 4:00PM</td>
      <td>305</td>
      <td>nyils6ih</td>
      <td>MLADENAVA</td>
    </tr>
    <tr>
      <td>ITP207</td>
      <td>Networking 2</td>
      <td>3</td>
      <td>BSIT 1-21</td>
      <td>S</td>
      <td>7:00AM - 10:00AM</td>
      <td>304</td>
      <td>cys2kis2</td>
      <td>RPCALAPAN</td>
    </tr>
    <tr>
      <td>ITC204</td>
      <td>Data Structures and Algorithms</td>
      <td>3</td>
      <td>BSIT 1-21</td>
      <td>T</td>
      <td>4:00PM - 7:00PM</td>
      <td>LAB A</td>
      <td>d2puo332</td>
      <td>RELLANA</td>
    </tr>
    <tr>
      <td>ITP205</td>
      <td>Multimedia Technology 2</td>
      <td>3</td>
      <td>BSIT 1-21</td>
      <td>T</td>
      <td>9:00AM - 12:00PM</td>
      <td>LAB A</td>
      <td>aghsqk3i</td>
      <td>MLDELOSSANTOS</td>
    </tr>
    <tr>
      <td>ITP204</td>
      <td>Cloud Computing</td>
      <td>3</td>
      <td>BSIT 1-21</td>
      <td>TH</td>
      <td>7:00AM - 10:00AM</td>
      <td>301</td>
      <td>wg4dhnf7</td>
      <td>JABARRANTES</td>
    </tr>
    <tr>
      <td>GE106</td>
      <td>Purposive Communication</td>
      <td>3</td>
      <td>BSIT 1-21</td>
      <td>W</td>
      <td>5:00PM - 8:00PM</td>
      <td>301</td>
      <td>z5acy5n4</td>
      <td>RHGUMBAN</td>
    </tr>
  </tbody>
</table>
</div>
<div class="table10">
    <table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>ITP311</td>
      <td>Human Computer Interaction</td>
      <td>3</td>
      <td>BSIT 1-31</td>
      <td>F</td>
      <td>3:00PM - 6:00PM</td>
      <td>302</td>
      <td>igwdu4tz</td>
      <td>BDELLADIA</td>
    </tr>
    <tr>
      <td>ITP308</td>
      <td>Software Engineering</td>
      <td>3</td>
      <td>BSIT 1-31</td>
      <td>M</td>
      <td>7:00AM - 10:00AM</td>
      <td>302</td>
      <td>2n3ysxf7</td>
      <td>JABARRANTES</td>
    </tr>
    <tr>
      <td>NEMR301</td>
      <td>Methods of Research for IT</td>
      <td>3</td>
      <td>BSIT 1-31</td>
      <td>S</td>
      <td>10:00AM - 1:00PM</td>
      <td>304</td>
      <td></td>
      <td>RPCALAPAN</td>
    </tr>
    <tr>
      <td>ITP319</td>
      <td>System Integration & Architecture</td>
      <td>3</td>
      <td>BSIT 1-31</td>
      <td>T</td>
      <td>1:00PM - 4:00PM</td>
      <td>304</td>
      <td>2is7ihqg</td>
      <td>MLDELOSSANTOS</td>
    </tr>
    <tr>
      <td>ITP 313</td>
      <td>Quantitative Methoods</td>
      <td>3</td>
      <td>BSIT 1-31</td>
      <td>T</td>
      <td>4:00PM - 7:00PM</td>
      <td>302</td>
      <td>hkek2fgr</td>
      <td>HABITANARA</td>
    </tr>
    <tr>
      <td>Elective 1</td>
      <td>ITE Elective 1</td>
      <td>3</td>
      <td>BSIT 1-31</td>
      <td>TH</td>
      <td>1:00PM - 4:00PM</td>
      <td>LAB B</td>
      <td>uiwdq7zz</td>
      <td>RRCENTENO</td>
    </tr>
    <tr>
      <td>ITP309</td>
      <td>Relational Database Maintenance System (RDBMS)</td>
      <td>3</td>
      <td>BSIT 1-31</td>
      <td>TH</td>
      <td>4:00PM - 7:00PM</td>
      <td>LAB A</td>
      <td>lfaockzj</td>
      <td>MLDELOSSANTOS</td>
    </tr>
    <tr>
      <td>GE308</td>
      <td>Science, Technology, and Society</td>
      <td>3</td>
      <td>BSIT 1-31</td>
      <td>W</td>
      <td>4:00PM - 7:00PM</td>
      <td>302</td>
      <td>cpfisfz6</td>
      <td>SMPANDAO</td>
    </tr>
  </tbody>
</table>
</div>
<div class="table11">
    <table>
  <thead>
    <tr>
      <th>CODE</th>
      <th>COURSE DESCRIPTION</th>
      <th>UNITS</th>
      <th>SECTION</th>
      <th>DAY</th>
      <th>TIME</th>
      <th>ROOM</th>
      <th>CLASSCODE</th>
      <th>FACULTY</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>ITP420</td>
      <td>Internship (500 hrs)</td>
      <td>6</td>
      <td>BSIT 1-41</td>
      <td>TBA</td>
      <td>TBA</td>
      <td>TBA</td>
      <td>33iijecl</td>
      <td>RRCENTENO</td>
    </tr>
    <tr>
      <td>ITP421</td>
      <td>Capstone Project and Research 2</td>
      <td>3</td>
      <td>BSIT 1-41</td>
      <td>TBA</td>
      <td>TBA</td>
      <td>TBA</td>
      <td>2t6wueih</td>
      <td>RRCENTENO</td>
    </tr>
    <tr>
      <td>Elective 3</td>
      <td>ITE Elective 3</td>
      <td>3</td>
      <td>BSIT 1-41</td>
      <td>S</td>
      <td>4:00PM - 7:00PM</td>
      <td>LAB A</td>
      <td>wui57kiq</td>
      <td>RRCENTENO</td>
    </tr>
    <tr>
      <td>ITP318</td>
      <td>Information Assurance & Security 1</td>
      <td>3</td>
      <td>BSIT 1-41</td>
      <td>S</td>
      <td>12:00PM - 3:00PM</td>
      <td>302</td>
      <td></td>
      <td>RESCUDERO</td>
    </tr>
    <tr>
      <td>ITP319</td>
      <td>System Integration & Architecture</td>
      <td>3</td>
      <td>BSIT 1-41</td>
      <td>T</td>
      <td>1:00PM - 4:00PM</td>
      <td>304</td>
      <td>2is7ihqg</td>
      <td>MLDELOSSANTOS</td>
    </tr>
  </tbody>
</table>
</div>

    </div>
</body>

</html>