-- Add example books to demonstrate pagination (15+ more books)
INSERT INTO `books` (`isbn`, `title`, `author`, `publisher`, `publication_year`, `description`, `category_id`, `subcategory_id`, `language`, `pages`, `edition`, `total_copies`, `available_copies`, `purchase_price`, `is_reference`, `created_by`) VALUES

-- Fiction - Romance
('978-0-06-112009-1', 'Jane the Virgin', 'Gina Rodriguez', 'HarperCollins', 2014, 'A charming romantic comedy about unexpected love.', 1, 1, 'English', 285, '1st', 4, 4, 13.99, 0, 1),
('978-0-14-028330-4', 'Outlander', 'Diana Gabaldon', 'Delacorte Press', 1991, 'A time-traveling romance across centuries.', 1, 1, 'English', 688, '1st', 3, 3, 16.99, 0, 1),
('978-0-7432-7358-9', 'The Notebook', 'Nicholas Sparks', 'Grand Central Publishing', 1996, 'A timeless love story of two souls reunited.', 1, 1, 'English', 214, '1st', 5, 5, 14.99, 0, 1),

-- Fiction - Mystery
('978-0-345-33313-1', 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'Norstedts', 2005, 'A gripping mystery involving a missing person and corporate conspiracy.', 1, 2, 'English', 465, '1st', 4, 4, 16.99, 0, 1),
('978-0-06-112010-7', 'Murder on the Orient Express', 'Agatha Christie', 'Collins Crime Club', 1934, 'A classic locked-room mystery aboard a luxury train.', 1, 2, 'English', 256, '1st', 4, 4, 12.99, 0, 1),
('978-0-14-118707-2', 'The Da Vinci Code', 'Dan Brown', 'Doubleday', 2003, 'A thrilling mystery involving art, history, and ancient secrets.', 1, 2, 'English', 454, '1st', 5, 5, 15.99, 0, 1),

-- Fiction - Fantasy
('978-0-7432-7359-6', 'The Lord of the Rings', 'J.R.R. Tolkien', 'Allen & Unwin', 1954, 'An epic fantasy trilogy about the fight against dark forces.', 1, 3, 'English', 1178, 'Complete', 3, 3, 29.99, 0, 1),
('978-0-439-13596-4', 'Harry Potter and the Philosopher\'s Stone', 'J.K. Rowling', 'Bloomsbury', 1997, 'The magical journey of a young wizard begins at Hogwarts.', 1, 3, 'English', 309, '1st', 6, 6, 14.99, 0, 1),
('978-0-06-112011-4', 'The Chronicles of Narnia', 'C.S. Lewis', 'Geoffrey Bles', 1950, 'A classic fantasy series set in a magical world.', 1, 3, 'English', 768, 'Complete', 3, 3, 18.99, 0, 1),

-- Non-Fiction - Biography
('978-0-14-118708-9', 'Steve Jobs', 'Walter Isaacson', 'Simon & Schuster', 2011, 'Biography of the Apple founder and visionary.', 2, 4, 'English', 630, '1st', 4, 4, 17.99, 0, 1),
('978-0-7432-7360-2', 'Becoming', 'Michelle Obama', 'Crown Publishing', 2018, 'Memoir of the former First Lady of the United States.', 2, 4, 'English', 426, '1st', 5, 5, 18.99, 0, 1),
('978-0-345-33314-8', 'Elon Musk: Tesla, SpaceX and the Quest for a Fantastic Future', 'Ashlee Vance', 'Ecco', 2015, 'Biography of the entrepreneur behind Tesla and SpaceX.', 2, 4, 'English', 679, '1st', 3, 3, 16.99, 0, 1),

-- Science - Physics
('978-0-06-112012-1', 'The Grand Design', 'Stephen Hawking', 'Bantam Books', 2010, 'Exploring the universe and the nature of reality.', 3, 6, 'English', 405, '1st', 3, 3, 17.99, 0, 1),
('978-0-14-118709-6', 'Cosmos', 'Carl Sagan', 'Random House', 1980, 'A journey through space and time exploring the cosmos.', 3, 6, 'English', 978, '1st', 2, 2, 19.99, 0, 1),
('978-0-7432-7361-9', 'The Elegant Universe', 'Brian Greene', 'W. W. Norton', 1999, 'Exploring superstrings and the nature of reality.', 3, 6, 'English', 544, '1st', 3, 3, 18.99, 0, 1);
