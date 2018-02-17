-- source: https://github.com/datacharmer/test_db/blob/master/employees.sql

DROP TABLE IF EXISTS dept_emp,
dept_manager,
titles,
salaries,
employees,
departments;

/*!50503 set default_storage_engine = InnoDB */;

CREATE TABLE employees (
  emp_no      INT             NOT NULL,
  birth_date  DATE            NOT NULL,
  first_name  VARCHAR(14)     NOT NULL,
  last_name   VARCHAR(16)     NOT NULL,
  gender      ENUM ('M','F')  NOT NULL,
  hire_date   DATE            NOT NULL,
  PRIMARY KEY (emp_no)
);

CREATE TABLE departments (
  dept_no     CHAR(4)         NOT NULL,
  dept_name   VARCHAR(40)     NOT NULL,
  PRIMARY KEY (dept_no),
  UNIQUE  KEY (dept_name)
);

CREATE TABLE dept_manager (
  emp_no       INT             NOT NULL,
  dept_no      CHAR(4)         NOT NULL,
  from_date    DATE            NOT NULL,
  to_date      DATE            NOT NULL,
  FOREIGN KEY (emp_no)  REFERENCES employees (emp_no)    ON DELETE CASCADE,
  FOREIGN KEY (dept_no) REFERENCES departments (dept_no) ON DELETE CASCADE,
  PRIMARY KEY (emp_no,dept_no)
);

CREATE TABLE dept_emp (
  emp_no      INT             NOT NULL,
  dept_no     CHAR(4)         NOT NULL,
  from_date   DATE            NOT NULL,
  to_date     DATE            NOT NULL,
  FOREIGN KEY (emp_no)  REFERENCES employees   (emp_no)  ON DELETE CASCADE,
  FOREIGN KEY (dept_no) REFERENCES departments (dept_no) ON DELETE CASCADE,
  PRIMARY KEY (emp_no,dept_no)
);

CREATE TABLE titles (
  emp_no      INT             NOT NULL,
  title       VARCHAR(50)     NOT NULL,
  from_date   DATE            NOT NULL,
  to_date     DATE,
  FOREIGN KEY (emp_no) REFERENCES employees (emp_no) ON DELETE CASCADE,
  PRIMARY KEY (emp_no,title, from_date)
)
;

CREATE TABLE salaries (
  emp_no      INT             NOT NULL,
  salary      INT             NOT NULL,
  from_date   DATE            NOT NULL,
  to_date     DATE            NOT NULL,
  FOREIGN KEY (emp_no) REFERENCES employees (emp_no) ON DELETE CASCADE,
  PRIMARY KEY (emp_no, from_date)
)
;

-- just a little bit of sample data

INSERT INTO `departments` VALUES
  ('d001','Marketing'),
  ('d002','Finance'),
  ('d003','Human Resources'),
  ('d004','Production'),
  ('d005','Development'),
  ('d006','Quality Management'),
  ('d007','Sales'),
  ('d008','Research'),
  ('d009','Customer Service');

COMMIT;

-- important!

FLUSH TABLES;