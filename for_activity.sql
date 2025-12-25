CREATE DATABASE for_activity;
USE for_activity;

CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(50),
    name VARCHAR(100),
    age INT,
    grade VARCHAR(50)
);