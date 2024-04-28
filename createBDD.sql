-- Use the student_management database
USE student_management;

-- Creating the users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'superstaff', 'staff', 'teacher') NOT NULL
);

-- Creating the students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address VARCHAR(255),
    phone_number VARCHAR(15),
    teacher_id INT,
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Creating the teachers table
CREATE TABLE teachers (
    user_id INT PRIMARY KEY,
	first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    department VARCHAR(255),
    hire_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Creating the classes table
CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(255) NOT NULL,
    class_room VARCHAR(255),
    teacher_id INT,
    FOREIGN KEY (teacher_id) REFERENCES teachers(user_id)
);

-- Creating the enrollments table
CREATE TABLE enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
);

-- Creating the attendance table
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'excused') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
);





-- ------------------------------------------------------------------------------
-- Création exemples : 
-- 1. Inserting Data into the users Table
-- Assuming bcrypt hashes for 'test'
INSERT INTO users (email, password, role) VALUES
('admin@test.com', '$2a$12$NbceM6d11qGxB4wicNzG/eqoMD75/Sa8SvvYqblCVHvnepztLGcey', 'admin'),
('staff@test.com', '$2a$12$MT0z55m4GMnV/xOAu4sWdOIJjUiR5mqspbgGRVmgyP/6YPj8jIUUu', 'staff'),
('superstaff@test.com', '$2a$12$Thb7snCgSWqsRev/p5dZSO9tf822Yg7fGCPomjzYjUmSE274Fs/Ry', 'superstaff'),
('teacher1@test.com', '$2a$12$DuKm5MHs75WfR3UEbhrYKOHHAK.e/xzP9h1oRuXcn8KHdKYBonQ6.', 'teacher'),
('teacher2@test.com', '$2a$12$TzZDTPRlQW9NwYbjRjlwtuY2PfdPGbxWHnekpYheGiOExKg6n3ZBi', 'teacher');


-- 2. Inserting Data into the students Table
INSERT INTO students (first_name, last_name, email, date_of_birth, gender, address, phone_number, teacher_id) VALUES
('John', 'Doe', 'john.doe@example.com', '2004-09-01', 'male', '123 Elm St', '555-1234', 4),
('Jane', 'Smith', 'jane.smith@example.com', '2005-05-12', 'female', '456 Oak St', '555-5678', 5),
('Jim', 'Beam', 'jim.beam@example.com', '2003-07-21', 'male', '789 Pine St', '555-9012', 4);


-- 3. Inserting Data into the teachers Table
-- Linking teachers to their user accounts
INSERT INTO teachers (user_id, department, hire_date) VALUES
INSERT INTO teachers (user_id, first_name, last_name, department, hire_date) VALUES
(4, 'JoJo', 'LeRigolo', 'Mathematics', '2019-08-15'),
(5, 'JaJa', 'LaSmart', 'Science', '2020-01-10');


-- 4. Inserting Data into the classes Table
INSERT INTO classes (class_name, class_room, teacher_id) VALUES
('Algebra II', 'Room 101', 4),
('Biology', 'Room 102', 5);


-- 5. Inserting Data into the enrollments Table
INSERT INTO enrollments (student_id, class_id, enrollment_date) VALUES
(1, 1, '2023-09-01'),
(2, 2, '2023-09-01'),
(3, 1, '2023-09-01');


-- 6. Inserting Data into the attendance Table
INSERT INTO attendance (student_id, class_id, date, status) VALUES
(1, 1, '2023-10-01', 'present'),
(2, 2, '2023-10-01', 'absent'),
(3, 1, '2023-10-01', 'present');





