--if (log_file == nil) then
--  log_file = "C:/Projects/tmp/mysql-proxy.log"
--end
--local fh = io.open(log_file, "a+")

function read_query( packet )
  if packet:byte() == proxy.COM_QUERY then
  	error(string.sub(packet, 2))
--    print(string.sub(packet, 2))
--    fh:write(os.date('%Y-%m-%d %H:%M:%S, 0, 0, ') .. string.sub(packet, 2) .. "\n")
--    fh:flush()
--    proxy.queries:append(1, packet )
--    return proxy.PROXY_SEND_QUERY
  end
end

--function read_query_result (inj)
--  fh:write(os.date('%Y-%m-%d %H:%M:%S, ') .. inj.query_time .. ", " .. inj.response_time .. ", " .. string.sub(inj.query, 2) .. "\n")
--  fh:flush()
--end
