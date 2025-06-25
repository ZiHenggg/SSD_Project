-- snort.lua - Corrected Snort3 Configuration

-- Network variables
HOME_NET = 'any'
EXTERNAL_NET = '!$HOME_NET'

-- Include default configurations
include 'snort_defaults.lua'

-- Configure detection engine
detection = {
    search_method = 'ac_bnfa',
    search_optimize = true,
    max_pattern_len = 20,
}

-- Network configuration
network = {
    checksum_eval = 'all',
    checksum_drop = 'none',
}

-- Configure IPS mode
ips = {
    mode = 'tap',  -- Changed from 'inline' to 'tap' for monitoring
    variables = default_variables,
    rules = [[
        include $RULE_PATH/local.rules
    ]]
}

-- Configure alert outputs
alert_fast = {
    file = true,
    packet = false,
    limit = 100
}

alert_full = {
    file = true,
    limit = 10
}

alert_json = {
    file = true,
    fields = 'timestamp pkt_num proto pkt_gen pkt_len dir src_addr src_port dst_addr dst_port service rule gid sid rev class msg priority'
}

-- Configure packet logging
output = {
    logdir = '/var/log/snort'
}

-- Configure stream processing
stream = { }
stream_tcp = { }
stream_udp = { }
stream_icmp = { }

-- Configure port configurations
port_scan = { }

-- Configure normalizers
normalizer = { }

-- Configure service detection
appid = {
    app_detector_dir = '/usr/local/etc/appid'
}